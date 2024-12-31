from flask import Flask, request, jsonify, render_template
import pandas as pd
import re
import nltk
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer
import openai
import numpy as np

openai.api_key = "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"

app = Flask(__name__, template_folder='.')

df = pd.read_csv("C:/Users/Nikhil G/Desktop/Alden/AI search box/Alden_clients.csv", encoding='ISO-8859-1')

df = df.rename(columns={
    'Lead': 'lead',
    'Alden sector': 'alden_sector',
    'Category of the Keyword1': 'category_of_the_keyword1',
    'Category of the Keyword2': 'category_of_the_keyword2'
})

nltk.download('stopwords')
nltk.download('wordnet')
stop_words = set(stopwords.words('english'))
lemmatizer = WordNetLemmatizer()

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

def preprocess_text(text):
    text = re.sub(r'\W', ' ', str(text))
    text = text.lower()
    text = ' '.join([lemmatizer.lemmatize(word) for word in text.split() if word not in stop_words])
    return text

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

if 'embedding' not in df.columns:
    print("Generating embeddings for dataset...")
    df['embedding'] = df['keywords'].apply(lambda x: get_embedding(x))
    df.to_json('dataset_with_embeddings.json', orient='records')
else:
    print("Loading precomputed embeddings...")
    df = pd.read_json('dataset_with_embeddings.json')

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

if __name__ == '__main__':
    app.run(debug=True)
