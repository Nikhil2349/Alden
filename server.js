const mysql = require('mysql2/promise');
const { OpenAI } = require('openai'); 
const express = require('express');
const math = require('mathjs');
const cors = require('cors');
require('dotenv').config();
const fs = require('fs');
const app = express();
app.use(express.json()); 
const port = 3000; 
app.use(cors());

const openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });
const dbConfig = {
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
};


let Data = [];

async function getData() {
  try {
    const connection = await mysql.createConnection(dbConfig);
    const [rows] = await connection.execute('SELECT * FROM ai_search');
    await connection.end();
    return rows;
  } 
  catch (error) {
    console.error('Database error:', error.message);
    return [];
  }
}

async function preprocessData(rawData) {
  try {
      let structuredData = rawData.map((company) => ({
          companyId: company['Company ID'],
          lead: company.Lead,
          overview: company.Overview,
          aldenSector: company['Alden Sector'],
          keybenefits: company['Key Benefits'],
          keyFeatures: company['Key Features'],
          challenges: company.Challenges
      }));

      structuredData = structuredData.filter(row =>
          Object.values(row).every(value => value !== null && value !== undefined)
      );
      
      structuredData.forEach(row => {
          let sectorsArray = row.aldenSector.split(', '); 
          sectorsArray.forEach(sector => {
              Data.push({
                  ...row,
                  aldenSector: sector
              });
          });
      });

      Data = Data.map(row => ({
        ...row,
        keywords: `${row.aldenSector} ${row.keyFeatures} ${row.keybenefits} ${row.challenges}`.toLowerCase(),
      }));


      for (let row of Data) {
        if (!row.embedding) {
            row.embedding = await getEmbedding(row.keywords); 
            row.suggestions = await getSuggestions(row.keywords); 
        }
      }

      console.log('Preprocessing step is Done')
      return Data;
  } catch (error) {
      console.error('Data processing error:', error.message);
  }
}

async function getEmbedding(text) {
  try {
    const response = await openai.embeddings.create({
        input: text,
        model: "text-embedding-ada-002",
    });
    const embedding = response.data[0].embedding;
    return embedding;
  } catch (error) {
      console.error(`Error fetching embedding: ${error.message}`);
      return Array(1536).fill(0); 
  }
}

async function getSuggestions(context, num_suggestions = 20, retries = 3) {
  const prompt = `
    Generate ${num_suggestions} unique autocomplete search queries based on this context: "${context}". 
    Focus on how users naturally enter search queries when looking for specific features, products, or services in this sector. 
    The queries should reflect real user behavior, using concise, intent-driven phrases that include relevant features, benefits, or challenges. 
    Avoid overly generic terms and prioritize phrases that highlight the specific needs or functionality users might search for. 
    Keep the queries short, natural, and directly tied to the context.
  `;
  for (let attempt = 1; attempt <= retries; attempt++) {
    try {
      const response = await openai.chat.completions.create({
        model: "gpt-3.5-turbo",
        messages: [
          { role: "system", content: "You are an assistant generating autocomplete search queries." },
          { role: "user", content: prompt }
        ],
        max_tokens: 200,
        temperature: 0.7,
      });
      const suggestionsText = response.choices[0].message.content.trim();
      const suggestions = suggestionsText
        .split('\n')
        .map(s => s.replace(/^\d+\.\s*/, '').trim())
        .filter(s => s);

      return suggestions.slice(0, 20).map(suggestion => suggestion.replace(/[^a-zA-Z0-9\s]/g, '').trim());
    } catch (error) {
      console.error(`Error generating suggestions for "${context}" (Attempt ${attempt} of ${retries}): ${error.message}`);
      if (attempt === retries) {
        return [];
      }
    }
  }
}

async function getUniqueSectors(Data) {
  try {
      const sectors = Data.map(row => row.aldenSector);
      const uniqueSectors = [...new Set(sectors)];
      uniqueSectors.sort();
      return uniqueSectors;
      console.log(uniqueSectors)
  } catch (error) {
      console.error('Error extracting unique sectors:', error.message);
  }
}

async function main() {
  const rawData = await getData(); 
  if (rawData.length > 0) {
    Data = await preprocessData(rawData);
  } 
  else {
    console.log('No data found.');
  }
}
main();

app.get('/get-sectors', async (req, res) => {
  try {
    const sectors = await getUniqueSectors(Data);
    if (sectors) {
      res.json({ success: true, sectors: sectors });
    } else {
      res.json({ success: false, message: 'No data found.' });
    }
  } catch (error) {
    res.json({ success: false, message: 'Error fetching sectors', error: error.message });
  }
});

app.get('/suggestions', async (req, res) => {
  const sector = req.query.sector?.toLowerCase();
  if (!sector) {
    return res.status(400).json({ error: 'sector not provided' });
  }
  try {
    const data = Data;
    const matchingRows = Data.filter(row => row.aldenSector.toLowerCase() === sector);
    if (matchingRows.length === 0) {
      return res.json({ success: false, message: 'No suggestions found for this sector.' });
    }
    const suggestions = matchingRows.flatMap(row => row.suggestions);
    return res.json({ suggestions });
  } catch (error) {
    console.error('Error fetching suggestions:', error.message);
    return res.status(500).json({ message: 'Error fetching suggestions', error: error.message });
  }
});

function cosineSimilarity(vec1, vec2) {
  const dotProduct = vec1.reduce((sum, v, i) => sum + v * vec2[i], 0);
  const magnitude1 = Math.sqrt(vec1.reduce((sum, v) => sum + v * v, 0));
  const magnitude2 = Math.sqrt(vec2.reduce((sum, v) => sum + v * v, 0));
  return dotProduct / (magnitude1 * magnitude2);
}

async function get_similarities(sector, input_text) {
  const data = Data;
  const inputEmbedding = await getEmbedding(input_text); 
  const similarRows = Data.filter(row => row.aldenSector.toLowerCase() === sector.toLowerCase())
      .map(row => {
          const similarity = cosineSimilarity(inputEmbedding, row.embedding); 
          return { lead: row.lead, similarity };
      });
  return similarRows.sort((a, b) => b.similarity - a.similarity)
  .map(row => row.lead)
  .reduce((list, leadValue) => {
      list.push(leadValue); 
      return list;
  }, []);
}

app.post('/get_similarities', async (req, res) => {
  const { sector, input } = req.body;
  try {
      const leadValues = await get_similarities(sector, input);
      res.json(leadValues); 
  } catch (error) {
      console.error(`Error in get_similarities: ${error.message}`);
      res.status(500).json({ error: 'An error occurred while processing the request' });
  }
});

app.listen(port, () => {
  console.log('Serving server on ', {port});
});
