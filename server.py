import mysql.connector
import openai
import json
import os
import re
from flask import Flask, jsonify,request
from flask_cors import CORS
import math
import numpy as np


app = Flask(__name__)
CORS(app)



def get_data():
    try:
        # Establish a connection to the database
        connection = mysql.connector.connect(**db_config)
        cursor = connection.cursor(dictionary=True)
        
        # Execute the query
        cursor.execute("SELECT * FROM ai_search")
        
        # Fetch all rows
        rows = cursor.fetchall()
        
        # Close the connection
        cursor.close()
        connection.close()
        
        return rows
    
    except mysql.connector.Error as err:
        print(f"Database error: {err}")
        return []

data = []
def preprocess_data(raw_data):
    try:
        structured_data = [
            {
                'companyId': company['Company ID'],
                'lead': company['Lead'],
                'overview': company['Overview'],
                'aldenSector': company['Alden Sector'],
                'keybenefits': company['Key Benefits'],
                'keyFeatures': company['Key Features'],
                'challenges': company['Challenges']
            }
            for company in raw_data
        ]

        structured_data = [
            row for row in structured_data if all(value is not None and value != '' for value in row.values())
        ]

        for row in structured_data:
            sectors_array = row['aldenSector'].split(', ')
            for sector in sectors_array:
                data.append({**row, 'aldenSector': sector})

        for row in data:
            row['keywords'] = f"{row['aldenSector']} {row['keyFeatures']} {row['keybenefits']} {row['challenges']}".lower()

        all_data_file = 'All_Data.json'
        saved_embeddings = {}

        if os.path.exists(all_data_file):
            try:
                with open(all_data_file, 'r', encoding='utf-8') as file:
                    saved_embeddings = json.load(file)
            except Exception as error:
                print(f"Error reading embeddings file: {error}")
                raise

        for row in data:
            if row['keywords'] in saved_embeddings:
                row['embedding'] = saved_embeddings[row['keywords']]['embedding']
                row['suggestions'] = saved_embeddings[row['keywords']]['suggestions']
            else:
                print('Computing embeddings for new data...')
                row['embedding'] =  get_embedding(row['keywords'])
                row['suggestions'] =  get_suggestions(row['keywords'])

                saved_embeddings[row['keywords']] = {
                    'companyId': row['companyId'],
                    'lead': row['lead'],
                    'overview': row['overview'],
                    'aldenSector': row['aldenSector'],
                    'keybenefits': row['keybenefits'],
                    'keyFeatures': row['keyFeatures'],
                    'challenges': row['challenges'],
                    'suggestions': row['suggestions'],
                    'embedding': row['embedding']
                }

        with open(all_data_file, 'w', encoding='utf-8') as file:
            json.dump(saved_embeddings, file, indent=2)

        print('Preprocessing step is done')
        return data
    except Exception as error:
        print(f"Data processing error: {error}")

def get_embedding(text):
    try:
        response = openai.Embedding.create(
            input=text,
            model="text-embedding-ada-002"
        )
        embedding = response['data'][0]['embedding']
        return embedding
    except Exception as error:
        print(f"Error fetching embedding: {error}")
        return [0] * 1536  

def get_suggestions(context, num_suggestions=20, retries=3):
    prompt = f"""
    Generate {num_suggestions} unique autocomplete search queries based on this context: "{context}". 
    Focus on how users naturally enter search queries when looking for specific features, products, or services in this sector. 
    The queries should reflect real user behavior, using concise, intent-driven phrases that include relevant features, benefits, or challenges. 
    Avoid overly generic terms and prioritize phrases that highlight the specific needs or functionality users might search for. 
    Keep the queries short, natural, and directly tied to the context.
    """
    
    for attempt in range(1, retries + 1):
        try:
            response = openai.ChatCompletion.create(
                model="gpt-3.5-turbo",
                messages=[{"role": "system", "content": "You are an assistant generating autocomplete search queries."},
                          {"role": "user", "content": prompt}],
                max_tokens=200,
                temperature=0.7
            )
            suggestions_text = response['choices'][0]['message']['content'].strip()
            suggestions = [re.sub(r'^\d+\.\s*', '', s).strip() for s in suggestions_text.split('\n') if s]
            return [re.sub(r'[^a-zA-Z0-9\s]', '', s).strip() for s in suggestions[:num_suggestions]]

        except Exception as error:
            print(f"Error generating suggestions: {error}")
            if attempt == retries:
                return []

def get_unique_sectors(data):
    try:
        sectors = [row['aldenSector'] for row in data]
        unique_sectors = sorted(set(sectors))        
        return unique_sectors
    except Exception as error:
        print(f"Error extracting unique sectors: {error}")

def main():
    raw_data = get_data()

    if len(raw_data) > 0:
        data =  preprocess_data(raw_data)
    else:
        print('No data found.')
main()

@app.route('/get-sectors', methods=['GET'])
def get_sectors_api():
    sectors = get_unique_sectors(data)
    return jsonify({"sectors": sectors})

@app.route('/suggestions', methods=['GET'])
def get_suggestions():
    sector = request.args.get('sector', '').lower()
    if not sector:
        return jsonify({"error": "sector not provided"}), 400

    try:
        matching_rows = [row for row in data if row['aldenSector'].lower() == sector]
        if not matching_rows:
            return jsonify({"success": False, "message": "No suggestions found for this sector."})

        suggestions = [suggestion for row in matching_rows for suggestion in row['suggestions']]
        return jsonify({"suggestions": suggestions})
    except Exception as e:
        print(f"Error fetching suggestions: {e}")
        return jsonify({"message": "Error fetching suggestions", "error": str(e)}), 500

def cosine_similarity(embedding1, embedding2):
    return np.dot(embedding1, embedding2) / (np.linalg.norm(embedding1) * np.linalg.norm(embedding2))

def top_similarities(sector, input_text, data):
    input_embedding = get_embedding(input_text)    
    similar_rows = []
    for row in data:
        if row['aldenSector'].lower() == sector.lower():
            similarity = cosine_similarity(input_embedding, row['embedding'])
            similar_rows.append({'lead': row['lead'], 'similarity': similarity})
    
    sorted_leads = sorted(similar_rows, key=lambda x: x['similarity'], reverse=True)
    result = [row['lead'] for row in sorted_leads]
    print(result)
    return result

@app.route('/get_similarities', methods=['POST'])
def get_similarities_route():
    try:
        request_data = request.get_json()
        sector = request_data.get('sector')
        input = request_data.get('input')

        if not sector or not input:
            return jsonify({'error': 'Missing sector or input'}), 400
        
        lead_values =  top_similarities(sector, input,data)
        return jsonify(lead_values)

    except Exception as error:
        print(f"Error in get_similarities: {str(error)}")
        return jsonify({'error': 'An error occurred while processing the request'}), 500

if __name__ == '__main__':
    app.run(debug=False, port=3000)