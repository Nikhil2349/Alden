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

# Ensure to have your OpenAI API key
openai.api_key = os.getenv("openai")

# Initialize Flask app
from flask import Flask, request, jsonify, render_template

app = Flask(__name__, template_folder='.')

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

# Similarity function
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
