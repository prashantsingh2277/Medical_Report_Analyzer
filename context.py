import requests
from PIL import Image, ImageDraw, ImageFont
import io
import os
from dotenv import load_dotenv
import google.generativeai as genai
from tqdm import tqdm

# Load environment variables
load_dotenv()

# Configure API key
api_key = os.getenv("GOOGLE_API_KEY")
if api_key:
    genai.configure(api_key=api_key)

def chat(context):
    if api_key:
        formatted_question = (text+"This is the given context of my report now answer my quries according to this report description")
        
        model = genai.GenerativeModel('gemini-pro')
        response = model.generate_content(formatted_question)
        
        # Access content parts of the response
        content_parts = response.parts

        formatted_responses = []
        for part in content_parts:
            formatted_responses.append(part.text)

        return ' '.join(formatted_responses)  # Concatenate the list into a single string
    else:
        return "API key is not configured properly."