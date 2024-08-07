import streamlit as st
import google.generativeai as genai
from PIL import Image
import os
from dotenv import load_dotenv
import Tohindi
import ToParagraph
import disease
import medicine

st.set_page_config(layout="wide")

load_dotenv()

api_key = os.getenv("GOOGLE_API_KEY")
if api_key:
    genai.configure(api_key=api_key)
else:
    st.error("API key not found. Please set up your environment variables.")

def generate_description(image):
    img = Image.open(image)
    model = genai.GenerativeModel(model_name="gemini-1.5-flash")
    response = model.generate_content(["Describe this report in paragraph format", img])
    return response.text

def get_gemini_response(message):
    model = genai.GenerativeModel(model_name="gemini-1.5-flash")
    response = model.generate_content([message])
    return response.text

def chat_with_ai(prompt):
    response = get_gemini_response(prompt)
    return response

# Streamlit interface
st.title("Medical Report Analyzer")

# Sidebar for language selection
st.sidebar.header("Settings")
language = st.sidebar.radio("Select language", ("Hindi", "Manipuri", "Tamil", "Telugu"))

# Session state initialization for chat history
if "chat_history" not in st.session_state:
    st.session_state.chat_history = []

uploaded_file = st.file_uploader("Choose an image...", type=["jpg", "jpeg", "png"])

if uploaded_file is not None:
    image = Image.open(uploaded_file)

    description = generate_description(uploaded_file)

    col1, col2 = st.columns(2)

    with col1:
        st.image(image, caption="Uploaded Image.", width=300)

    with col2:
        st.write("Description:")
        st.write(description, key="description")

    Disease = disease.PredictDisease(description)
    st.text_area("Possible Disease", Disease, key="Disease", height=300)

    # Language-based translation and paragraph formatting
    if language == "Hindi":
        translated_text = Tohindi.ToHindi(description, "Hindi")
    elif language == "Manipuri":
        translated_text = Tohindi.ToHindi(description, "Manipuri")
    elif language == "Tamil":
        translated_text = Tohindi.ToHindi(description, "Tamil")
    elif language == "Telugu":
        translated_text = Tohindi.ToHindi(description, "Telugu")
    
    col1, col2 = st.columns(2)

    with col1:
        st.text_area("Translated Text", translated_text, key="translated_text", height=300)

    paragraph = ToParagraph.ToPara(translated_text, language)

    with col2:
        st.text_area("Paragraph Form", paragraph, key="paragraph", height=300)

    medicines = medicine.Med(Disease)
    st.text_area("Recommended Medicines", Disease, key="Medicines", height=300)
    with open("med.txt", "w", encoding="utf-8") as file:
        file.write(medicines)
    
    st.markdown('<a href="http://localhost/Medical_Report_Analyzer/index.php" target="_blank">Click here to chat with ai</a>', unsafe_allow_html=True)
