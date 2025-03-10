import streamlit as st
import pandas as pd
import pymysql
import numpy as np
from rembg import remove
from PIL import Image, ImageEnhance
import io
import zipfile
import os

# Database connection details
DB_HOST = "118.139.166.84"
DB_USER = "aibasedsols_aldenglobal"
DB_PASSWORD = "marketplace@123"
DB_NAME = "aibasedsols_aldenglobal"

# Function to insert data into MySQL
def insert_data_into_db(df, table_name):
    try:
        df = df.iloc[:, :5]  # Keep only the first 5 columns
        df = df.replace({np.nan: None})  # Handle NaN values

        # Connect to MySQL
        conn = pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASSWORD, database=DB_NAME)
        cursor = conn.cursor()

        # Create table if not exists
        cols = ", ".join([f"`{col}` TEXT" for col in df.columns])
        create_table_query = f"""
        CREATE TABLE IF NOT EXISTS `{table_name}` (
            `idno` INT AUTO_INCREMENT PRIMARY KEY,  
            {cols}
        );
        """
        cursor.execute(create_table_query)

        # Insert data (excluding idno)
        column_names = ", ".join([f"`{col}`" for col in df.columns])
        placeholders = ", ".join(["%s"] * len(df.columns))
        insert_query = f"INSERT INTO `{table_name}` ({column_names}) VALUES ({placeholders})"

        for _, row in df.iterrows():
            cursor.execute(insert_query, tuple(row.fillna("")))

        conn.commit()
        cursor.close()
        conn.close()
        return "✅ Data successfully inserted into the database!"

    except Exception as e:
        return f"❌ Error: {str(e)}"

# Streamlit UI
st.title("🛠️ Multi-Function Streamlit App")

# Buttons for selection
col1, col2 = st.columns(2)

with col1:
    excel_button = st.button("📂 Upload Excel to Database")

with col2:
    image_button = st.button("🖼️ Remove Image Background")

# Excel Upload Section
if excel_button:
    st.subheader("📂 Upload Excel and Insert into Database")
    
    uploaded_file = st.file_uploader("Upload Excel File", type=["xlsx"])
    
    if uploaded_file:
        excel_data = pd.ExcelFile(uploaded_file)
        sheet_names = excel_data.sheet_names
        
        sheet = st.selectbox("Select a Sheet", sheet_names)
        
        if st.button("Insert into Database"):
            df = pd.read_excel(excel_data, sheet_name=sheet)
            table_name = sheet.replace(" ", "_")
            result = insert_data_into_db(df, table_name)
            st.success(result)

# Image Background Removal Section
if image_button:
    st.subheader("🖼️ Remove Image Background & Enhance Quality")

    uploaded_files = st.file_uploader("Choose image files", type=["png", "jpg", "jpeg"], accept_multiple_files=True)

    final_images = []

    if uploaded_files:
        temp_dir = "temp_images"
        os.makedirs(temp_dir, exist_ok=True)

        for uploaded_file in uploaded_files:
            # Open image and ensure correct format
            image = Image.open(uploaded_file).convert("RGBA")

            # Convert to bytes for rembg processing
            img_byte_array = io.BytesIO()
            image.save(img_byte_array, format='PNG')
            img_byte_array = img_byte_array.getvalue()

            # Remove background
            output = remove(img_byte_array)
            output_image = Image.open(io.BytesIO(output)).convert("RGBA")

            # Create a white background to avoid transparency issues
            white_bg = Image.new("RGBA", output_image.size, (255, 255, 255, 255))
            white_bg.paste(output_image, (0, 0), output_image)
            final_image = white_bg.convert("RGB")

            # High-Quality Resizing to 200x200 pixels (LANCZOS for best quality)
            final_image = final_image.resize((200, 200), Image.LANCZOS)

            # Apply Sharpening to Restore Details
            enhancer = ImageEnhance.Sharpness(final_image)
            final_image = enhancer.enhance(1.5)  # Increase sharpness slightly

            # Convert to High-Quality JPG
            jpg_filename = os.path.splitext(uploaded_file.name)[0] + ".jpg"
            final_image_path = os.path.join(temp_dir, jpg_filename)

            # Save JPG with maximum quality and no blurriness
            final_image.save(final_image_path, format="JPEG", quality=100, optimize=True, subsampling=0)

            final_images.append(final_image_path)

            # Display before and after images
            col1, col2 = st.columns([1, 1])
            with col1:
                st.image(image, caption=f"Original: {uploaded_file.name}", use_container_width=True)
            with col2:
                st.image(final_image, caption=f"Final High-Quality JPG: {jpg_filename}", use_container_width=True)

        # Create and provide ZIP download option
        if final_images:
            zip_buffer = io.BytesIO()
            with zipfile.ZipFile(zip_buffer, "w", zipfile.ZIP_DEFLATED) as zip_file:
                for image_path in final_images:
                    zip_file.write(image_path, os.path.basename(image_path))

            zip_buffer.seek(0)
            st.download_button(
                label=f"📥 Download All Images ({len(final_images)})",
                data=zip_buffer,
                file_name="final_images.zip",
                mime="application/zip"
            )
