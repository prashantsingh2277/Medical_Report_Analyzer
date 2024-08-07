import sys
import os
import google.generativeai as genai
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Configure API key
api_key = os.getenv("GOOGLE_API_KEY")
if api_key:
    genai.configure(api_key=api_key)
else:
    print("API key is not configured properly.")
    sys.exit(1)

def chat(content):
    if api_key:
        formatted_question = (content)
        
        model = genai.GenerativeModel('gemini-pro')
        response = model.generate_content(formatted_question)
        
        # Access content parts of the response
        content_parts = response.parts

        formatted_responses = []
        for part in content_parts:
            clean_text = part.text.replace('*', '')
            formatted_responses.append(clean_text)

        return ' '.join(formatted_responses)  # Concatenate the list into a single string
    else:
        return "API key is not configured properly."

if __name__ == "__main__":
    if len(sys.argv) > 1:
        user_message = sys.argv[1]

        # Specify the path to your .txt file
        file_path = 'med.txt'

        # Read the file and store its content in a variable
        file_content = ""
        if os.path.exists(file_path):
            with open(file_path, 'r') as file:
                file_content = file.read()

        # Combine the content from the file and PHP input
        combined_content = file_content + " " + user_message

        # Get the response from the chat function
        response = chat(combined_content)

        # Output the response to be captured by PHP
        print(response)
    else:
        print("No input received.")
        sys.exit(1)
