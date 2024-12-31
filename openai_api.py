from flask import Flask, request, jsonify, render_template
import pandas as pd
import re
import nltk
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer
import openai
import numpy as np
import os
import requests
from flask_cors import CORS

# Enable CORS for all routes and origins
app = Flask(__name__, template_folder='.')
CORS(app)  # This line enables CORS

# OpenAI API key
openai.api_key = os.getenv("openai")

# Fetch dataset from GitHub (server-side request)
def fetch_dataset():
    url = "https://raw.githubusercontent.com/Nikhil2349/Alden/main/Alden_clients.csv"
    response = requests.get(url)
    if response.status_code == 200:
        return pd.read_csv(response.text)
    else:
        raise Exception("Failed to fetch dataset from GitHub")

# Load the dataset
try:
    df = fetch_dataset()
    df = df.rename(columns={
        'Lead': 'lead',
        'Alden sector': 'alden_sector',
        'Category of the Keyword1': 'category_of_the_keyword1',
        'Category of the Keyword2': 'category_of_the_keyword2'
    })
except Exception as e:
    print(f"Error fetching dataset: {e}")
    df = pd.DataFrame()  # Set an empty dataframe in case of an error

# Download NLTK resources
nltk.download('stopwords')
nltk.download('wordnet')
stop_words = set(stopwords.words('english'))
lemmatizer = WordNetLemmatizer()

# Fill missing data
df.fillna({
    'Overview': 'no_description',
    'category_of_the_keyword1': 'no_category',
    'category_of_the_keyword2': 'no_category',
    'alden_sector': 'no_sector'
}, inplace=True)

df['alden_sector'] = df['alden_sector'].str.lower().str.strip()
df['category_of_the_keyword1'] = df['category_of_the_keyword1'].str.lower().str.strip()
df['category_of_the_keyword2'] = df['category_of_the_keyword2'].str.lower().str.strip()

df['keywords'] = df['alden_sector'] + ' ' + df['category_of_the_keyword1']

# Preprocess the text by removing non-word characters, converting to lowercase, and lemmatizing
def preprocess_text(text):
    text = re.sub(r'\W', ' ', str(text))
    text = text.lower()
    text = ' '.join([lemmatizer.lemmatize(word) for word in text.split() if word not in stop_words])
    return text

# Get embeddings from OpenAI
def get_embedding(text):
    try:
        response = openai.Embedding.create(
            input=text,
            model="text-embedding-ada-002"
        )
        return response['data'][0]['embedding']
    except Exception as e:
        print(f"Error fetching embedding: {e}")
        return np.zeros(1536)  # Return zero vector in case of error

# Check if embeddings are already computed
if 'embedding' not in df.columns:
    print("Generating embeddings for dataset...")
    df['embedding'] = df['keywords'].apply(lambda x: get_embedding(x))
    df.to_json('dataset_with_embeddings.json', orient='records')
else:
    print("Loading precomputed embeddings...")
    df = pd.read_json('dataset_with_embeddings.json')

# Function to calculate similarity
def get_top_5_similarities(sector, input_text):
    input_combined = preprocess_text(sector + " " + input_text)
    input_embedding = get_embedding(input_combined)
    
    df['similarity'] = df['embedding'].apply(
        lambda x: (np.dot(input_embedding, x) / 
                   (np.linalg.norm(input_embedding) * np.linalg.norm(x)) if np.linalg.norm(x) > 0 else 0) * 100
    )
    df['similarity'] = df['similarity'].clip(0, 100)
    top_5_results = df.nlargest(5, 'similarity')
    return top_5_results[['lead', 'Overview', 'similarity']]

# Function to get unique alden sectors
def get_unique_alden_sectors(df):
    unique_sectors = set()
    for sectors in df['alden_sector'].dropna():
        for sector in sectors.split(','):
            unique_sectors.add(sector.strip().capitalize())
    return list(unique_sectors)

@app.route('/')
def index():
    alden_sectors = get_unique_alden_sectors(df)
    return render_template('Alden.html', alden_sectors=alden_sectors)

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
