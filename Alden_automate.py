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

if "app_choice" not in st.session_state:
    st.session_state.app_choice = "Home"

def check_duplicates(cursor, table_name, row):
    conditions = " AND ".join([f"`{col}` = %s" for col in row.index])
    check_query = f"SELECT EXISTS(SELECT 1 FROM `{table_name}` WHERE {conditions})"
    cursor.execute(check_query, tuple(row))
    return cursor.fetchone()[0]  

def insert_data_into_db(df, table_name):
    try:
        if 'date' in df.columns:
            df['date'] = pd.to_datetime(df['date'], errors='coerce').dt.strftime('%Y-%m-%d')

        df = df.iloc[:, :6]  
        df = df.replace({np.nan: None})

        conn = pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASSWORD, database=DB_NAME)
        cursor = conn.cursor()

        cols = ", ".join([f"`{col}` TEXT" for col in df.columns])
        create_table_query = f"""
        CREATE TABLE IF NOT EXISTS `{table_name}` (
            `idno` INT AUTO_INCREMENT PRIMARY KEY,  
            {cols}
        );
        """
        cursor.execute(create_table_query)

        column_names = ", ".join([f"`{col}`" for col in df.columns])
        placeholders = ", ".join(["%s"] * len(df.columns))
        insert_query = f"INSERT INTO `{table_name}` ({column_names}) VALUES ({placeholders})"

        inserted_count = 0
        skipped_count = 0

        for _, row in df.iterrows():
            if not check_duplicates(cursor, table_name, row.fillna("")):
                cursor.execute(insert_query, tuple(row.fillna("")))
                inserted_count += 1
            else:
                skipped_count += 1

        conn.commit()
        cursor.close()
        conn.close()

        return f"✅ Inserted {inserted_count} new records. Skipped {skipped_count} duplicates."

    except Exception as e:
        return f"❌ Error: {str(e)}"

st.title("🔀 Multi-Function App")

col1, col2 = st.columns(2)
with col1:
    if st.button("📊 Google Sheets to MySQL"):
        st.session_state.app_choice = "Google Sheets to MySQL"
with col2:
    if st.button("🖼️ Image Background Remover"):
        st.session_state.app_choice = "Image Background Remover"

st.markdown("---")

# Google Sheets to MySQL Section
if st.session_state.app_choice == "Google Sheets to MySQL":
    st.title("📊 Google Sheets to MySQL")

    sheet_url = st.text_input("🔗 Paste Google Sheet Link:")

    if sheet_url:
        try:
            sheet_id = sheet_url.split("/d/")[1].split("/")[0]
            csv_url = f"https://docs.google.com/spreadsheets/d/{sheet_id}/gviz/tq?tqx=out:csv"

            df = pd.read_csv(csv_url)

            df.columns = df.columns.str.strip()
            df.columns = df.columns.astype(str)

            df_filtered = df.iloc[:, :6]

            st.dataframe(df_filtered.head())

            table_name = 'website_speakers'

            if st.button("📥 Insert into Database"):
                if table_name:
                    result = insert_data_into_db(df_filtered, table_name.replace(" ", "_"))
                    st.success(result)
                else:
                    st.error("⚠️ Please enter a valid table name.")

        except Exception as e:
            st.error(f"❌ Error loading data: {str(e)}")

# Image Background Remover Section
elif st.session_state.app_choice == "Image Background Remover":
    st.title("🖼️ Image Background Remover")

    uploaded_files = st.file_uploader("Choose image files", type=["png", "jpg", "jpeg"], accept_multiple_files=True)

    final_images = []

    if uploaded_files:
        temp_dir = "temp_images"
        os.makedirs(temp_dir, exist_ok=True)

        for uploaded_file in uploaded_files:
            image = Image.open(uploaded_file).convert("RGBA")

            img_byte_array = io.BytesIO()
            image.save(img_byte_array, format='PNG')
            img_byte_array = img_byte_array.getvalue()

            output = remove(img_byte_array)
            output_image = Image.open(io.BytesIO(output)).convert("RGBA")

            white_bg = Image.new("RGBA", output_image.size, (255, 255, 255, 255))
            white_bg.paste(output_image, (0, 0), output_image)
            final_image = white_bg.convert("RGB")

            final_image = final_image.resize((200, 200), Image.LANCZOS)

            enhancer = ImageEnhance.Sharpness(final_image)
            final_image = enhancer.enhance(1.5)

            jpg_filename = os.path.splitext(uploaded_file.name)[0] + ".jpg"
            final_image_path = os.path.join(temp_dir, jpg_filename)

            final_image.save(final_image_path, format="JPEG", quality=100, optimize=True, subsampling=0)

            final_images.append(final_image_path)

            col1, col2 = st.columns([1, 1])
            with col1:
                st.image(image, caption=f"Original: {uploaded_file.name}", use_container_width=True)
            with col2:
                st.image(final_image, caption=f"Final High-Quality JPG: {jpg_filename}", use_container_width=True)

        if final_images:
            zip_buffer = io.BytesIO()
            with zipfile.ZipFile(zip_buffer, "w", zipfile.ZIP_DEFLATED) as zip_file:
                for image_path in final_images:
                    zip_file.write(image_path, os.path.basename(image_path))

            zip_buffer.seek(0)
            st.download_button(
                label=f"Download All Images ({len(final_images)})",
                data=zip_buffer,
                file_name="final_images.zip",
                mime="application/zip"
            )
