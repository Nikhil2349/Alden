import requests
import pandas as pd
import io
import re
import nltk
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer
import openai
import numpy as np
import os
from flask import Flask, request, jsonify, render_template
from flask_cors import CORS
import time
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# Ensure to have your OpenAI API key
openai.api_key = os.getenv("openai")  # Ensure the API key is set in environment variables

# Initialize Flask app
app = Flask(__name__, template_folder='.')
CORS(app, resources={r"/get_similarities": {"origins": "https://alden.onrender.com"}})

# Function to fetch dataset from GitHub
def fetch_dataset():
    url = "https://raw.githubusercontent.com/Nikhil2349/Alden/main/Alden_clients.csv"
    response = requests.get(url)
    if response.status_code == 200:
        # Use io.StringIO instead of pandas.compat.StringIO
        df = pd.read_csv(io.StringIO(response.text))
        print("Columns in the dataset:", df.columns)  # Print columns for debugging
        return df
    else:
        raise Exception("Failed to fetch dataset from GitHub")

# Load dataset
df = fetch_dataset()

# Renaming columns based on the dataset structure
df = df.rename(columns={
    'Lead': 'lead',
    'Alden sector': 'alden_sector',
    'Category of the Keyword1': 'category_of_the_keyword1',
    'Category of the Keyword2': 'category_of_the_keyword2'
})

# Initialize NLTK resources
nltk.download('stopwords')
nltk.download('wordnet')

stop_words = set(stopwords.words('english'))
lemmatizer = WordNetLemmatizer()

# Handle missing data
df.fillna({
    'Overview': 'no_description',
    'category_of_the_keyword1': 'no_category',
    'category_of_the_keyword2': 'no_category',
    'alden_sector': 'no_sector'
}, inplace=True)

# Clean up text columns
df['alden_sector'] = df['alden_sector'].str.lower().str.strip()
df['category_of_the_keyword1'] = df['category_of_the_keyword1'].str.lower().str.strip()
df['category_of_the_keyword2'] = df['category_of_the_keyword2'].str.lower().str.strip()

# Combine sectors and categories as keywords
df['keywords'] = df['alden_sector'] + ' ' + df['category_of_the_keyword1']

# Text preprocessing function
def preprocess_text(text):
    text = re.sub(r'\W', ' ', str(text))
    text = text.lower()
    text = ' '.join([lemmatizer.lemmatize(word) for word in text.split() if word not in stop_words])
    return text

# OpenAI embedding function
def get_embedding(text):
    try:
        response = openai.Embedding.create(
            input=text,
            model="text-embedding-ada-002"
        )
        return response['data'][0]['embedding']
    except Exception as e:
        print(f"Error fetching embedding: {e}")
        return np.zeros(1536)

# Generate or load embeddings
if 'embedding' not in df.columns:
    print("Generating embeddings for dataset...")
    df['embedding'] = df['keywords'].apply(lambda x: get_embedding(x))
    df.to_json('dataset_with_embeddings.json', orient='records')
else:
    print("Loading precomputed embeddings...")
    df = pd.read_json('dataset_with_embeddings.json')

# Similarity function to calculate cosine similarity
def get_top_5_similarities(sector, input_text):
    # Preprocess input text
    input_combined = preprocess_text(sector + " " + input_text)
    input_embedding = get_embedding(input_combined)

    # Compute cosine similarity between input text embedding and dataset embeddings
    df['similarity'] = df['embedding'].apply(
        lambda x: (np.dot(input_embedding, x) / 
                   (np.linalg.norm(input_embedding) * np.linalg.norm(x)) if np.linalg.norm(x) > 0 else 0) * 100
    )
    
    # Sort by similarity and return the top 5
    top_5_results = df.nlargest(5, 'similarity')
    return top_5_results[['lead', 'Overview', 'similarity']]

# Get unique sectors for the dropdown
def get_unique_alden_sectors(df):
    unique_sectors = set()
    for sectors in df['alden_sector'].dropna():
        for sector in sectors.split(','):
            unique_sectors.add(sector.strip().capitalize())
    return list(unique_sectors)

# Home route
@app.route('/')
def index():
    alden_sectors = get_unique_alden_sectors(df)
    return render_template('Alden.html', alden_sectors=alden_sectors)

def autocomplete(row, num_suggestions=20, retries=3):
    context = f"{row['alden_sector']} {row['category_of_the_keyword1']} {row['Overview']}"
    prompt = (
        f"Generate {num_suggestions} unique autocomplete search queries based on this context: {context}. "
        "Each query should sound like a real human trying to find a specific product or related information, "
        "using natural language. Avoid questions, and focus on terms and phrases a person would use to search for products, "
        "features, or services in this category. Keep the queries short and relevant."
    )
    for attempt in range(retries):
        try:
            response = openai.ChatCompletion.create(
                model="gpt-3.5-turbo",
                messages=[
                    {"role": "system", "content": "You are an assistant generating autocomplete search queries."},
                    {"role": "user", "content": prompt},
                ],
                max_tokens=200,  # Adjust based on expected response size
                n=1,
                temperature=0.7
            )
            suggestions = response['choices'][0]['message']['content'].split('\n')
            cleaned_suggestions = [re.sub(r'^\d+[\).\s]*', '', s.strip()) for s in suggestions if s.strip()]
            return cleaned_suggestions[:num_suggestions]
        except Exception as e:
            print(f"Error generating autocomplete (Attempt {attempt+1}): {e}")
            time.sleep(2)
    # Fallback if all retries fail
    return [row['keywords'], row['alden_sector'], row['category_of_the_keyword1']] * (num_suggestions // 3)

# Generate suggestions and store in a global list
autocomplete_suggestions = []
for _, row in df.iterrows():
    suggestions = autocomplete(row)
    autocomplete_suggestions.extend(suggestions)

@app.route('/suggestions')
def get_suggestions():
    # Get the selected category from the query parameter
    category = request.args.get('category', '').lower()

    if not category:
        return jsonify({"error": "Category not provided"}), 400

    # Perform cosine similarity between the category and autocomplete suggestions
    top_suggestions = get_top_suggestions(category)

    return jsonify(top_suggestions)


def get_top_suggestions(category):
    # Combine category with suggestions to calculate similarity
    suggestions_with_category = [category] + autocomplete_suggestions
    
    # Use TF-IDF Vectorizer to convert text to vector form
    vectorizer = TfidfVectorizer()
    tfidf_matrix = vectorizer.fit_transform(suggestions_with_category)
    
    # Calculate cosine similarity between the first item (category) and the rest of the list
    cosine_sim = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:])
    
    # Get the top 10 most similar items (you can adjust this number if needed)
    similar_items = cosine_sim[0]
    
    # Create a list of (suggestion, similarity score) tuples
    suggestions_with_scores = [
        (autocomplete_suggestions[i], similar_items[i]) for i in range(len(similar_items))
    ]
    
    # Sort suggestions based on similarity score in descending order
    sorted_suggestions = sorted(suggestions_with_scores, key=lambda x: x[1], reverse=True)
    
    # Return only the top 10 suggestions
    top_suggestions = [suggestion for suggestion, score in sorted_suggestions[:10]]
    
    return top_suggestions

# Similarity route
@app.route('/get_similarities', methods=['POST'])
def get_similarities():
    try:
        data = request.get_json()
        sector = data['sector']
        input_text = data['input_text']
        top_5_results = get_top_5_similarities(sector, input_text)
        results = top_5_results.to_dict(orient='records')
        return jsonify(results)
    except Exception as e:
        return jsonify({"error": str(e)}), 400

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=port, debug=True)
