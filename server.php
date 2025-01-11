<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alden Solution Providers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            padding-top: 50px;
        }
        .header {
            position: absolute;
            top: 10px;
            left: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        h1 {
            font-size: 32px;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
            width: 80%; /* Adjusted for better form width */
            max-width: 600px; /* To limit the max width */
            margin: 0 auto;
        }
        .row {
            display: flex;
            gap: 15px;
            width: 100%;
        }
        select {
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
        input[type="text"] {
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
        .search-row {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
            position: relative;  /* To position the suggestions below this row */
        }
        button {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 10%; /* To make button full width */
        }
        button:hover {
            background-color: #45a049;
        }
        .search-icon {
            font-size: 18px;
        }
        #search-results {
            margin-top: 20px;
            padding: 10px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            margin: 20px auto;
        }

        .result-item {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .result-item:hover {
            background-color: #f0f0f0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .result-item strong {
            font-size: 18px;
            color: #333;
        }

        .result-item p {
            font-size: 16px;
            color: #555;
            margin: 8px 0;
        }

        .result-item .similarity {
            font-size: 14px;
            font-weight: bold;
            color: #4CAF50;
        }

        .no-results {
            text-align: center;
            font-size: 18px;
            color: #888;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        #loading-spinner {
            text-align: center;
            margin-top: 20px;
            font-size: 18px;
            color: #4CAF50;
            font-weight: bold;
        }

        #suggestion-box {
            border: 1px solid #ccc;
            top: 100%;
            height: auto;
            overflow-y: auto;
            background-color: #fff;
            width: calc(100% - 24px); /* Ensure the width matches the input box */
            position: absolute; /* Position relative to the parent container */
            z-index: 1;
            display: none;
            border-radius: 5px;
            padding: 5px;
            z-index: 9999;
            overflow-y: auto;
        }

        .suggestion-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .suggestion-item:hover {
            background-color: #f1f1f1;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item p {
            margin: 0;
            font-size: 14px;
            color: #333;
        }
        .card {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            width:750px; /* Adjust width as needed */
            justify-content: center;
            align-items: center;
            margin-bottom:20px;
        }

        .card-image img {
            width: 100px;
            height: 100px;
            border-radius: 5px;
            margin-right: 20px;
            float: left;
        }

        .card-content {
            margin-left: 120px;
        }

        .card-content h2 {
            font-size: 30px;
            margin-bottom: 10px;
        }
        .card-content p {
            font-size: 18px;
        }



        .card-footer {
            font-size: 17px;
            margin-top: 10px;
        }

    </style>
</head>
<body>

    <div class="header">Alden Solution Providers</div>

    <h1>Explore Softwares</h1>

    <div class="form-container">
        <!-- Sector Dropdown Row -->
        <div class="row">
            <select id="sector" required>
                <option value="" disabled selected>----- Select the Sector -----</option>
                <!-- Sectors will be loaded dynamically via AJAX -->
            </select>
        </div>

        <!-- Search Input and Button Row -->
        <div class="search-row">
            <div style="display: flex; flex-direction: row;">
                <input type="text" id="search-text" placeholder="Enter software requirement" required oninput="showSuggestions()">
                <button onclick="searchSoftware()">
                    <span class="search-icon">🔍</span>
                </button>
            </div>
            <div id="suggestion-box" style="border: 1px solid #ccc; display: none;"></div>
        </div>
    </div>
    <div id="loading-spinner" style="display: none;">Loading...</div>
    <div id="search-results">
        <div class="card" id='Brainvire'>
            <div class="card-image">
                <img src="data:image/png;base64,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" alt="Ramco Logistics Logo">
            </div>  
            <div class="card-content">
                <h2>Brainvire</h2>
                <p>Brainvire track and trace service uses AI, IoT sensors, GPS, and RFID technology. The technology enables instant alerts and data-driven insights, helping businesses respond promptly to any disruptions and maintain optimal supply chain performance.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: IT Services and IT Consulting</p>
            </div>
        </div>
        <div class="card" id='Cogoport'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Cogoport</h2>
                <p>Cogoport excels in strengthening supply partnerships globally. With a focus on operational excellence and strategic collaboration, Cogoport provides comprehensive solutions across Ocean Freight, Air Freight, Haulage, and Trucking.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Transportation, Logistics, Supply Chain and Storage</p>
            </div>
        </div>
        <div class="card" id='Crest'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Crest</h2>
                <p>Crest brings together cutting-edge technology and deep supply chain expertise. It is an AI-powered tool that helps in automating demand forecasts, streamlining inventory, generating insights to help your business expand. It also helps in reduction in stock-out and cash recovery time.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Utilities Pineville</p>
            </div>
        </div>
        <div class="card" id='Datalabs India Solutions Private Limited'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Datalabs India Solutions Private Limited</h2>
                <p>fruiSCE Transportation Management AI-based algorithms help clients to manage end-to-end transportation and fleet management requirements. You can enhance load planning, resource allocation, and trips with all options required to track, trace, PoD, expenses management along with fuel and driver management.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Information Technology & Services</p>
            </div>
        </div>
        <div class="card" id='Elivia Inc'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxMSEBUREhMVFhUXGBUXGRgYGBgfIBoWHRcXGhkXGBseHikjHx8mHxUVITElJSsrLi8wGx8zODMsNygtLisBCgoKDg0OGxAQFy0lHiAtLS0rLSstLSsrKy0tLS0tLS0uLS0uLS8tMy0tLS0tLTAtNzctLi03LS0tLS02LSstMP/AABEIAMgAyAMBIgACEQEDEQH/xAAcAAACAwEBAQEAAAAAAAAAAAAABwQFBgEDAgj/xABEEAABAwICBQoDBgMHBAMAAAABAAIDBBEGIQUSMUFRBxMiMmFxgZGhsUJSwRRicpLR8COCwhZTorLS4fEVQ1RjNHOD/8QAGgEAAQUBAAAAAAAAAAAAAAAAAAECAwUGBP/EAC4RAAIBAgUDAwMDBQAAAAAAAAABAgMRBAUSITEyQVETIrFCcZFhodEUgeHw8f/aAAwDAQACEQMRAD8AeKEIQAIQhAAhCEACEIQBmNL43pqeYwu13OGTi0CzTwzIv4LRU87ZGNew3a4BwPEHMLB4h5P5Jql00UrA2R2s4O1rtJ22sM+O5bfRlEIYWQtNwxobc77b0rtYDN8pNVNHSAwlzQXgPc3aG2O/cCbfsqp5LKyd5la5znRAAguJNn32AnsubdyYT2AixFwdxXzDE1g1WtDRwAACL7WA9EhtPSzfapDMXCTWN7k5Z5W7OCfN14zU7H9ZrXd4BSxdgEXTaeqo+pUSjs13W8jkryg5QqtnXLJR95tj5tsmNV4Xo5OtTx/yjV9W2VBpDk2p3ZxSPjPA9IfQ+qdqi+QO6L5R6d9hMx0R49ZvmM/Ra2irY5W68T2vbxaQUpdLYFq4blrRK3jHmfy7fK6oKWrlgfrRudG8cCQe4/ojQnwB+gkJb4f5Rjkyrb/+jR/mb+nkmDSVbJWB8bw9p2EG4TGmgPdCEJABCEIAEIQgAQhCABCFnsT4sho7NcC+Q56jTsHFx3ISuBoUKow3p6OsiMkYLSDZzTtB+oVujgDiFFr6+OFhfK8NaN59gN5WA07juR92045tvzHrHu3D97FJTpSnwcmJxlLDr3vfx3N7pDSkMAvLI1vecz3DaVltIcoUTcoY3P7XdEd42n0CXksrnuLnOLidpJuT4r4XbDCRXO5RV85qy2pqy/LNRVY7qndXUZ3Nv/muq6bE9W7bO/wsPYKoQplSguxXyxdefM3+Sx/67U/+RL+dy94cUVbdk7/Gx9wqdCXRDwN9esvqf5ZqaTHlU3rajx2ix9Leyv8AR/KDC7KVjo+0dIfr6JboUcsPTfY6aWZ4mn9V/vuPCg0jFMNaKRrx2HZ3jaFF0zh6nqh/FjBdueMnDx+huEnYJ3McHMcWuGwg2K2Og8ePbZlSNYfONo7xv8PVc08LKO8S3w2cU57VFZ/sU+JMDTU93x3liGdwOk0feb9R6Km0Hp2akfrxOy+Jp6ru8fVO6irWTMD43BzTvCyuLMEMnBlpwGS7S3Y1/fwPb/yoFLsy5UlJXRb4ZxNFWM6PRkA6UZOY7RxHarxfn9rpaaa41o5WHuIKbWDcVNrGaj7NmaMxucPmb+iSUbboU06EITABCEIAEIUeuqmxRulebNYCSexAFVi3ELaOHWyMjriNvE/MewZeiTZMtTNvklkd4klSMQaYfVzumfs2Nb8rM7D1TG5P8MfZ4xUSj+M8ZA/Aw7u87/LipelCFng/QH2ODVJvI86zzuvbqjsH6r0xLiKOkZn0pD1WD3PALmKNPtpI77ZHZMb9T2BKasqnyvdJI4uc43JKlo0db1S4KrMcxVD2Q6vg99LaVlqZNeV1zuG5o4NChIWowzg99RaSW8cW0fM4dnAdv/K7pSjTXhGcp06uJnZbtmcpaV8rtSNjnOO4C61ui8ASvsZniMfKOkfHcPVb3R2jYoGakTA0dm09pO0lTFxVMVJ9Oxf4fJqcd6ru/wBjN0eCaSPawvPF5PsLD0VtDoinZ1YYx3Mb+inJP6WxxVvlfqP5tlyA0NbkM9pIvdQapy7lpDD0odMV+BtfZI/kb5BRptDU7+tDGe9jf0Se/tXW/wDkSealU+Oa5n/e1hwc1p+l0umXke6cHyhg1uCKR/Va6M8WOPsbhZjSeAZmZwuEg4Honu4HzC+tHcpjhYTwg9sZt/hN/dbjQmm4athfC69si0ixae0Jyq1Idzjq5dh6n02+2wmqindG4te0tcNzhYrzTs0pomKobqysDuB3juO5LXFGFn0vTadeIm1ztHAOH1C7KWIjPZ7MocZlc6C1R3iVmh9MS0z9eJ3e07HDtCauH9Px1bLtyeOsw7R+o7Um1J0fWvgkbLGbOafMcD2Ja1BTV+4zA5hPDys94+P4GdjDCzKxms2zZmjou+b7juzt3JSxCaCcBocyZjrAWzDuFt+3xT5pZtdjX/MAfMXQ6mYXa5Y0uGx1hfzVcpW2Zr07q6PqAktaXCzrC47bZr0QhMFBCEIAFh+VV0v2aMMB5svPOW8NS/Zt8QFuF8uaCLFKnZgKXk8w/wDaJ+ekb/CiIOexz9w8Np8OKaGk65kETpXmzWi/edwHadiksYGiwAA4BLblE0zzkop2noR5u7X/AOw9zwUtOLqTscmNxKw9Jy79vuZvS+kn1ErpXnM7Bua3c0KGhanA2HvtEnOyD+Ew7Pmdw7hv/wCVZSapx/QyNKnPE1bLllhgzCYcBUVDctrGHfwc7s4BMELoCVXKFpipZWujbI+NjQwsDXFt7tBLstuesPBVcpupK7NhhsNDDw0x/wCjWQqbCNVLLRQyTX1yDcneNYhrvEAFXKjOkFQ1mEKOWQyPhBc4kmznAE8SAbK+Qi4FB/Yyh2fZ2/mf/qUOq5P6J3Va9n4Xn+q61aEt2AstKcmsjc4JQ/7rxqnzGR9FfYCw1JRtkfKRrSao1Qb2AvtPHP0WuUTSNeyCMySOs0fuw4lLdvYbJqKu+Duka5kEZkkdZo9ewcSlLiPTz6uS5yYD0WcO08SjEenpKuS5yYOozh2niVUKwoUNG75MtmOYus9EOn5BXuFsOvq5Lm4iaek7j91vb7eQPML4dfVv3tiaek7+lvb7eQLYoqRkTGxxtDWtFgAkr19PtXI7LsudV+pPp+T1YwAADYMl9oQq41KVjAcqlTM1sQYXNiOtrFtxd2Vg7wv+wpfJhUTPpn86XFgcBGXd3SAPAZeq2MjA4WcARwIuusaALAADgE6+1gPpCEJoGXx7p6SkhaYgNd7i3WIvqgC5NuP+6h8n2JZqrnI5rOLA1weABcE2sbZfsrUaT0bFURmOZge3bY7jxBGYO1eeiNDw0rSyBgaDmcyST2k+KW6sB3TdcIKeSY/C027XbGjxJASUlkLnFzjckkk8SdqYPKdXWjjhHxEuPc3IX8XeiXisMLC0b+TLZzX11VBcR+We1FSulkbGzrOIA/fmnVouhbBCyJmxot3nefE3KwfJpo7WlfORkwarfxHafL/MtzpuSRtNK6IXkDHFvfY7BvKhxU7y0+DvybD6afqvl/BPUepoopLc5Gx9tms1pt3XSkwZpKpNdGA+R2u7+ICSQW/ETfhmnGuVqxdHAF1CEgAhCEAcQhRNJV7IIzJI6zR+7DiUJXGykoq74O6Rr44IzJIbNHqeA4lKXEenpKuTWOTB1GcBxPEoxHp6Srk1nZMHUZwHE8SqhWVCho3fJlsxzF1noh0/IK8wxh19W++bYmnpO4/db2+3oTDGHX1b7m7YmnpO/pb2+3oWxR0rImNjjaGtaLABJXr6fbHkXLsudZ659PycoqRkTGxxtDWtFgApCzGKsYMontj5syPI1iNbVAbc2zsc8jkrTD+mGVcAmYCBcgtO1rha49QfFV7T5ZqUklZFohCEgoLF8oOJZqXm44bNc8OcXkA2AysAct62irtL6Ghqmhs7NYDMG5BHcR+9iVWvuBUYD0/JVwuMoGuxwbrAW1gRfZx2oV5ozRsVPHzcLA1ozsL5neSTmT/suJHzsBST46pGTmEudkdUvA6AO/O9/ECy04KWlTybymoOrKwQlxNzfWAvstaxPimTGwNaGjYAAO4Jzt2AVfKFUa1a5vyNa301v6lmlZ4ml1qyc/8AscPym30VYrWmrQRhsVLXXk/1Y2sCUnN0TMs33efE5egatCouiYdSCJnysYPJoCxmJsevp6l0MUbXBhAcXXzOV9WxFvVVcryk2bPDw0Uox8JG5jp2NJc1jQTtIABPeV6qFoevFRBHM0EB7b2O47x53U1MJgQhCAOIQomkq9kEZkkdZo9ewcShK42UlFXfB3SVfHBGZJHWaPU8BxKUuI9PSVcms7Jg6jOA4niUYi09JVyazsmDqM4DieJVQrKhQ0bvky2Y5i6z0Q6fkFeYXw6+rfc3bE09J39Le328gjC+HX1b7m7YmnpO/pb2+3kC2KOlZEwRxtDWtFgAkr19O0eRcuy51n6lTp+TlHSMiY2ONoa1osAFIQhV/JqUklZGWxbg9ta9sgk5t4GqejcFtyRlcZ5lWuHdDMpIBCwl2Zc5x+JxyJtu2AeCtCd6iUek4ZSWxSxvI2hrgbeSLuwpMQhCQAQhfLxkbbUARhpKHnOa52PnPk1m635b3QlDDhet+1BnNyB4ffnbHV29fX2dvFcT9K8gOpcKTEuK637UX848OD7c1nq7epqenFOZpyzyTXGwCS07/wDKn/8Atl/zlQlZ4mi1aycf+xx/Mb/VVit4dKMJWVqsvux8RdUdwWc03gqnqZuecXtcba2qRZ1t+YOeQV3ombXgif8AMxh82gr50npaGnaHTSBgOQvtPcBmVU7pm4g7xTPajpWxRtjYLNaAAOwKQo1FWRzMEkTw9p2EFSU0ecQhRNJV8cEZkkdZo9TwHEoSuNlJRV3wGkq+OCMySOs0ep4DiUpcR6ekq5NZ2TB1GcBxPEruI9PSVcms7Jg6jOA4niVUKyoUNG75MtmOYuu9EOn5BXmF8Ovq33N2xNPSdx+63t9vQmF8Ovq33N2xNPSdx+63t9k2KOlZEwRxtDWtFgAkr19PtjyOy7LnVeup0/JyjpWRMEbGhrW5ABSEIVdyahJJWR1CEIFKDHchbo+cg2yaPAvaCPUhLHAzyNIQWvm4g9xa663/ACnVOrQlvzvY3y6X9KxHJzBraQjO5ge4/kI93BSR6WA5UIQowBCF8SvDWlx2AEnuCAPtCWdNykSOqAHRMEJcBYX1g2+297E+CE7SwGAdFw85z3Mx858+q29+N1MVNiTEMVFGHSXJcSGtbtNtu3cMvNGG8QxVjHOju0tIDmu2i+w92R8klnyBgeUKm1K1zvna13pq/wBKzSYnKdRXjjmHwuLT3O2eo9Uu1aYeWqCMZmVL08RL9d/yNnAdXzlEwb2EsPgbj0LVT8ouHZ6h8csLdfVaWltxcZ31hfv9AoPJppHVlfATk8azfxDaPEZ/ypkLgrLRUZpcuq+rh4vxt+DMYB0LLS0zmzZOe/W1b31RYDPty9lqF41NSyMa0j2sHFxAHmV9xyBwDmkEHMEG4Khe+53EfSNcyCN0shs0ep3AdqUmI9PSVcms7Jg6rOA4niVreVAO5uG19TWdf8Vhq+mul4u/C01bV3M1nGKm5+itkv3BXuF8Ovq33N2xNPSdx+63t9kYXw6+rfc3bE09J3H7re32TXo6VkTBGxoa1osAE6vX0+2PJHl2XOq/UqdPyco6VkTBGxoa1osAFIQhV3JqEklZHUIQgUEIUPS2kGU8L5n9Vov3nc0dpNggBdcqukdaaOnByjaXO/E7YPIf4lJ5JaHOaoIysIx/md7MWGrap88zpHZvkdfLiTkB7J14Y0V9lpY4fiAu/wDGcz+ngpJbRsBbIQhRgC4QuqPV1scQ1pZGMGy7nAC/igDPwYFpGTiYNfkdYMJ6AO7K1/Amy6tHDK1zQ5rg5pzBBuCOwoS3YGL5VNHl9MyYf9p1j+F9hfzazzWW5ONJ8zWhhNmyjUP4trPXL+ZNjSVG2aF8T+q9pafHf9UhqqB8EzmHJ8biMtxado8k+O6sA9NM0IngkiPxNIHYdoPgQCkpLEWuLXCxaSCOBG1OTDelRVUzJhtIs4cHjrD6+IWJ5RdD83KKhg6MmTux/HxHseKnws9MtLKTOcNrgqq7c/YylHUuikbIw2c0gjwTp0VXNnhZKzY4X7jvHgbhJALT4IxD9nk5qQ/wnn8ruPcdhU+Jpa43XKK7KsYqNTRLpfyTOVmmk1opczEGlvYH3Jz7xb8qr8A4p+zu5iY/wXHIn4Hf6T/vxTQrqRk0TopBrMcLEfX2SZxThySjksbujceg/iOB4OXDFpqzNWOWuo454zHIA5jv2CCsq3k9h1785Jq/Llfzt9FmMIY1dTWhmu+HYD8TO7iOxNGhrY5mCSJ4e07x7HgexClOGyZBVwtKq05xu0fVHSsiYI2NDWjIAL3QhRk6SSsjqEIQKCEIQB8PcACSbAZknglDjrE/2uTm4yeZYcvvu+f3t4psaRpedhkiJtrscy/DWBF/VLPR3J3OZ9WYtbEDm5rrlw4NG7xT4WXIH3ybYe5yT7XIOgw9C/xP+bub79yaS8aWnbGxsbAGtaAABuAXsmyd2AIQhIAJOcomlOerHMB6MQ1B+L4z55fypm4o0sKWmfL8VtVg4vOz9fApJ0VM+eZsbc3yOA8Sdp90+C7gNPkwhe2hu4mzpHlg+7kPdrkLTaPpGwxMiZ1WNDR4BdTG9wJCWvKjoSzm1bBkbMk7/hd9PAJlKPXUrJY3RPF2vBBHYlTswFTyeaf+zz8082ilIH4X/C7x2Hw4Jp6RomzROieLtcLH6EdoyKSOINEPpJ3Qv3Ztd8zdzvRMbk/xPz8Yp5XfxWDIn42Df3jf58bPkvqQ2SUlZmE0zox9NM6J+7Ydzm7nD98VBThxNoFlXFqnJ7bljuB4HsKUtbSPhkMcjdVzTmP3uVhRrKat3Mjj8FLDzuul8fwbDBmLdS1PUO6Oxjzu+67s7dy3dbRxzRmORoexwzB9x29qRq0uGsXSU1o5Lvi4b2/hPDs9lFWw9/dE7MvzTQlTq8dmfOJcByw3kp7yx7bfG0d3xeHkszo/SU1O/Wie5jt9t/YRsPinhozSsVQ3WieHcRvHYRuUTTOGKapzkjAd87cnee/xuuTU1szRRkpK8XdGN0XylPFhPEHfeYbH8py9loablBondZz2fiYf6brPaR5M3jOCZrhweCD5i9/IKknwNXN/7IcOLXt/W6LRY4Y39tqD+/H5JP8ASrPReloahpfDIHgGxtcEHtBzCTv9kq3/AMd/p+q3HJ3h2emMkkw1NcBoZcE5G+sbZfspHFJcgbWVxDSQLkA2HE8EmqLFNaapruce5xeAY7nVNz1NXYNtk6FDboyESc8IoxJ8+q3W87JqaQEtfL3AC5NgFB0tpiGmbrSvA4DaT3BLbEmK5Kq7G3ZF8t83fiP099qkpUZT+xw4vH08Ot3d+C2xPjZxcY6V1mjbJbaeDb7u1V2hMZTxSDnXmSMnMHaBxafp7LMKVo2gfPK2KMXcfQbyexWHowjGzRm/67EVKqkpO/j/AAO+N4IBGwi4X2vGlh1GNYPhaG+Qt9FjuUPE/MsNNE7+I8dIj4Gnd3n27wqtK7sjYq9tzK8oGn/tNRzbD/CiuB953xO+g/3V1yXaD61Y8cWR/wBTvp5rH4c0M+rnbE3IbXu+Vu8p4UdM2KNsbBZrQGgdgT5OysKe6EIUYAhCEAUOLcPNrIdXISNuY3dvynsP6JOES002+OWN3iCF+gVl8Y4UbWM12WbM0ZHc4fK79U+MrbMD7wfihlYzVdZszR0m8fvN7PZS8RYfjq2Wd0XjqvG0dh4jsSYcJaab4o5WHuIKb+CdOurKcveAHsdqOI2OyBDhw27ErvF3QypTjUi4yV0xZ6X0RLTP1JW24OGxw4gqCnlW0UczCyRoc07j7jgsFp3Ab23fTHXb8h6w7jsPj6rtpYlPaWzM1i8pnTeqluv3MdT1D43BzHFrhvabFavRePpmWEzRIOPVd+h8gspPA5ji17S1w3OFivNTSpwnyiupYitQftdv98DUosc0j+s50Z+80+4uraHTtM7qzxH+dvskqhQPCR7MsYZ3WXVFMeX/AFCH+9Z+Yfqo82nqZvWnj/O0nyCSqEiwa8kjzyfaC/I063HVKzqF0h+60j1dZZnSmPJ5LiJrYhx2u8zl6LJIUscNCPa5x1s0xFTa9l+h9zzOe4ue4ucdpJuT4r4XrTUz5HasbXOcdzRfxWz0FgJxs+pNh/dtOf8AMd3h5p86kYLcgo4WtiJe1f3MxoTQstU/VjGXxOOxvefomroDQUdJHqsF3HrPO1x+g7FOpKRkTAyNoa0bgsdi3HTYrxUxD5Nhftazu+Y+i4KtaVTZcGlwWXQw/ue8vP8ABYYzxW2kYY2EOncMh8g+Z30CU8MUtTMGi8kkjvEk7SUQQy1M2q0Okkee8k8Sfqm5hDCzKNms6zpnDpO4D5W9nv7M2iixJWFdANo4dQZvdYvdxPAdg3K7QhRcighCEACEIQAIQhAFNp7DVPV251pDhse3J1uF9471L0TouOmiEUTbNGfaTlck8VOQi4FdpjTENKznJnaoJsBa5J7AjQ2mYapmvC64BsRaxB7QqXHmHJKyOMxEa8Zd0TkHB1r58eiPVcwFhuSjZI6UjXk1ei03ADdbaePSPknWVgNDX6Pimbqysa8do2dx3LL1/J/C7OJ7o+w9IeufqtkVmIsd0jp+ZBfm7VD7DUJ773t2kWToVJx6Wc9bC0avXG5lqrANS3qGN47CQfUfVV0uFKxu2B3gWn2KcK+XuAFyQBxKlWKmjgnk1B8NoTX9nar+4k8l7RYTrHbICO8tHuU3PtLPnb+YLxl0pA3rTRN73tH1Tv6ufgYsko95MXdLgCpd13RsHeSfQW9Vf6PwBAyxlc6Q8OqPIZ+qs6rGFFHtnafwXd/lBVBpDlLiGUML3ni8ho9Ln2THWqyOqlleGp/Tf7mzoqGOFurExrR2C3nxVfpvE1NSg848F/yNzd5bvGyWGlsaVc9xznNt+WPo/wCLb6qooNHyzv1YmOe7fYepO7xUejuzuUVFWReYjxrPVXY3+FEfhac3D7zvpsVdoDD81W+0TeiOs89VvjvPYtlh/k5As+rdf/1tOX8zv081vqanZG0MY0NaMgALAIckuBxVYcw5DRsswXees87T+g7PdXSEKMAQhCABCEIAEIQgAQhCABCEIAEIQgD5kYCCDsIIKWsHJtIKgXlYYQ69xfWLb7LWtfxQhKm0AzEuuVaGYmIgOMIBva9g++13ha3ihCWPIC6svWKle7qsce5pKEKYQsKbDVZJ1aeXxaWjzdZXlDyc1T85HRxjtOsfJuXqhCjc2KajRnJ3TR2MpdMe3ot8hn6rVUtKyNupGxrGjc0ADyC6hRttgeyEIQAIQhAAhCEACEIQB//Z" alt="">
            </div>  
            <div class="card-content">
                <h2>Elivia Inc</h2>
                <p>Elixia offers an Intelligent Delivery Orchestration Platform, integrating advanced logistics modules for streamlined operations, cost efficiency, collaboration, and real-time visibility across the supply chain. It provides end-to-end visibility and optimization through predictive analytics for enhanced supply chain efficiency.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Chemicals, Consumer Goods, Ecom, Logistics, Manufacturing, Oil & Energy, Pharmaceutical, Pharmaceuticals</p>
            </div>
        </div>
        <div class="card" id='Inciflo'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Inciflo</h2>
                <p>Inciflo is an IoT-enabled cloud-based Inventory and Warehouse Management System. Our platform empowers businesses with advanced technology to efficiently manage batches and track the product across the supply chain, from manufacturing - distributors – retailers.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Software Development</p>
            </div>
        </div>
        <div class="card" id='Inquizity'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Inquizity</h2>
                <p>A leading SaaS-based platform that provides end-to-end sales & operations planning, and supply chain automation. Inquizity helps businesses lower inventory carrying costs by leveraging AI-based demand planning. It enables customers to reduce cost, while improving margins and boosting sales through real-time tools like the EDIFICE control tower.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: </p>
            </div>
        </div>
        <div class="card" id='Intugine Technologies'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Intugine Technologies</h2>
                <p>Intugine renders Logistics visibility across multimodal transportation like FTL, PTL, Ocean, Courier & Rail through multi-faceted solutions like GPS Tracking & SIM. It provides Control Tower for exception mitigation, automated freight & indent management for cost-effective logistics, and a unified interface that streamlines operations. Partnered with India’s National Logistics Policy, it optimizes operational efficiency through utilizing AI & data-driven approach.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Transportation, Logistics, Supply Chain and Storage</p>
            </div>
        </div>
        <div class="card" id='Lepton Software'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Lepton Software</h2>
                <p>Lepton solutions bring multi-disciplinary knowledge from geospatial and remote sensing domains. Using the latest technologies AI/ML, Big Data, and industry leading mapping platforms like Google Maps and Map info, Lepton enhances decision-making with location based solutions.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: IT Services and IT Consulting</p>
            </div>
        </div>
        <div class="card" id='NEWAGENXT'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>NEWAGENXT</h2>
                <p>NewageNXT uses the latest technology, including multi-factor authentication and secured API gateways, ensuring robust data security and seamless integration. This helps in improving efficiency, providing real-time updates and accessibility on the go. Users have seen an increase in operational efficiency, leading to faster decision-making and improved CX.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Information Technology & Services</p>
            </div>
        </div>
        <div class="card" id='ORMAE'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>ORMAE</h2>
                <p>ORMAE provides customized end-to-end (from algorithm creation to UI/UX design) solutions for Operations Research(OR) and data problems in areas of operations, planning, logistics, forecasting and trading. We leverage advanced algorithms and data techniques to deliver precise solutions to the challenges the client is facing and ensure a sustained adoption.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Business Consulting and Services</p>
            </div>
        </div>
        <div class="card" id='Qbit'>
            <div class="card-image">
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAxlBMVEX///8Akf9YWVtTWFoAj/8Ajf9OU1VUVVfu7+9MTU/MzM2AgYMAi//y8vL8/PyNjY5gYWLGxsf3/P8Ak/8Ahf91eHq8vL3i9P+23P9qbm/N5v/Z6f/w+f9Wp/9jsf/z+v+bzv9Dqv+Eu//D4v8Agf8yof9suf+w2P/f8P/c3d0tm/8Al//m8f/S6/+ay/9FS02ExP+wsLGXmps5p/9mtf+Syv+j0v+y1v8Unv96wf/i4uKRxP+hoaNcrP99uf/F3v+s0P8AfP+ERtxyAAAL+klEQVR4nO2cCVfiOhSAW0lwoSK2aAtWKHYKtaWKwzK4jfP+/596Sdm63HRhUWfO/c55b7TQmo/syQ2ShCAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgnx7VHOBqn51Sg5AMJjedd7HDmf8/v77zjKCf8Yz+NV3mopCKdlAKVWU5rwza5tfnbwd6d48Oz5zk2EI1byJ8RdL6g+OIrRbWyr+nf3VKd2Om4448+JQxRv8fRnZ7sjF9JbF1RnUvzrJpXD7Sgm/RWHt/UVlVbU0Ws4vdCTPf0tRdTtb+HFoL/jqtBfCkLcU5NXxbyip05IVMOE4FTz2vtaK8vM8Lx1Psfe3aiexV19+Xmy4LiU42UmQKVrwcxvVGI8/c9Jx/3gc4/Qi9nLrNPLa42VxP7Wj7CYoy01Q8aRaiVG9yulczk6PYhzXYv6xV48vCvdU6mTrKpiTi3s2vNzWsL8HQQbQ3OzZ8Ol4O8PRzkV0geYe2vBiO0Njx0ZmDe2k/uJ+Deu1rQxdf1+GMp0d1vCkdbSN4fveBFlrox/U8OVoG8NZbiUMp/bhf7mfBekf1DDeWRQ0HHrZSaaKNp68vs4G1utrvzdXcuYeSvuQhk/bGGb2hFS7HbW7m5UntTs0Jl5WVpL3QxrGm9Jihm5Gaqn8DM0ZzI9xRkZq8Vv2a9jawlDc11PfEi4buh2hI+kd0LBRvqVxxcWtn+6+Ixie6KNRYtPh0oYvx6dx3iKvHSVovOQaPgvSSYiRc6fuCG6lDzsZSveXcSLTrbfjhOFx65qRpakLGlLqZGbgAkEbRcbRTCxvKFavVZN5eLSYYL2Jb/qAaxNxkh03hClY9KA3hzA8fztK5uDGUzyt7oGGxCsiyHDA2+kow7BSWaTm5ezyiXF9eV/M+KxxKvJjhifC++DhjFZ0XSnw4RKQZVitvbxcXzQqbLrPqFYrjdpZTPL+OsZl+OLJozADMw0N0FBJDZ+FWOAD/EglTuchu/AYu1h9rN1Hbkg0padP/GqyDylqCLakZFxYUFLHUDmNtsNpQ4BqZMEF7g+3NOyC1VBrC94OYUCfEX0tachy9ewghkOoGtFJCUFJ8oAPKTqsKWhYbawSuVfDNlSLSLnl6xmUiVppw0p1tWi4V8MpkDrSK7eH7ULloLnp84saVh5PdjAU9YfQqJtapQThFQJls+hW2LD6lGUotU5Pj6EOo8paW/Eq8y2QOFqws18DFYRIf1Pc8Oo8y7DOBgi1lOJx440NG87AhHGgVsIvKQhWZrrZxChsWKm8ZBmGlhcpxftUeqKYwLCblGtJGWr6ITL9nWkYbmCkri77xKz54WXCMG9+CI25SldDSQUGpyTLsFpp1S4uamnvt/0bakAN+ihrKHUAw/d1g5wel17dn/N0XacHrHs3tIHyVWpAswBokTMNV33XRXJe1fquhg/lDBurvuvsEXzl0IZegbn9ngxfkhVxMXD7hwxPWsmX7v8xw/OrLzH0y4eNbGtYryWbmrPPMDx8W/qphkB/SMobluwP14bSz4Mbgj3+r3wl+8/DYDM+Lzum+UxDaGpHn/P8gjnhwZmj1RTQhCYod9/DEBx55y1DGdrCiK5mynbJucVnGoILuiT7FnvdrNDlYswImh8OvokhtNSWHYKnRqaUisWvdKFpdGQjOMMwNTDdvyG4ivGadUd0CZnc8nIKNVdyc7PW86W9BbhORpxu0c8kXNoGN6C8zR1fawiuJmYW01jfxw274Jp3p4jhJ4xL4RXhaPJSRLcBwi04qJ2J7ZFmGDYqOxvWcgzhVX0o/m5FNNd51MUQqoWxJ5SZH5afPbXy4nFfS+/MRDdFNVuwt+NFNoHTc/zV8liyKa006mUNj47EG4cLBLtrlvgOMxKgQDRmAuzmx8p52rB1HSbrOllGq1dSnuF9UvA0L0hYFeyQZpTTYS+MMwmLN+2okptedFWiQQ7AWlu10biqpZqZSvVnecPj3D1zcPtPJvOMeXDXmlOi9cODGcqIVc3U7f4w03BhCayXPuUapqJNWGuaY/hLEE6RHamgstJp9nn+8wh9O/GMyHKw0BDiMXfNWzpPGR4dN64zWxtXGG2SP0984O0oZSVyEG9Q47F7xfctWie5hsmYqJBq6ynLUXQGgciDjLskieexwWsjr7ODaHUm8Z2P4obLDaQykXvLd5xmFdVAGFyq9MTZqI8Uj72q+3SRi0bUMB5MVXz/8KyA4TW8jZgRbSKp4gBhok3a0G5pfThi7SfRWCa7Y14HeS5u7rqN31PYsCEVMEw1pvmGcKjBKrWK85DMSHfQIYtbeENq8u5Qm/HHLD8pJREPV3iXe3XCp0wkeyFDQYexcmQdw/h59tEOGO2Pad8jm+OlvDtUuSKf0rcX/SJ5T3RQRSMV1iPM7NMI8G53tqGde96XUkXWGHLybDB9NyWdF1Q6qktBOLpJrdVBPT6kuN7qzDZMxtAWMcwOg86Gjs3FoEZhP3TZg9ILWanIvcrbxWNqJLcJp8kzvIfi93IMXXh+UAg+Xw6Pa9A5a28suZeaPicNeczrZWLI9tiKbFbnGIKKOYaStcOhJ8qcwr6CaKzhsdN7AufxDHsMG5STt/U+N//hLZrAlGGys3tpJaMyjo/yRqe7HFyjjrk8saE44ID9rNXYUFmGlEj1659X4ZWrt8t48uq1Rpx0MMLZ21XsHS1xMMYSPbM9zVNkLepiZETkDu9AzRtjcBMZfJ9HiaqEF9Kffv38XHQL/Ez4LXHcXQ7nKc9s1rj4iAjpzH7PFYY/+WYHgwNth1xkXf96CrXuLonSKxt7dFgGouQXgdjQagbxyu9jHZJ2wa/CAA01XYI2CLLWCr6AQHR+ogBKR/oFfUD0e30RgdvbVpH23HVjE4M43+zrJKZlvtVk46eFe2ng2rAyyvubn0zQK10bWQexWLaC59I+uKbl3nDCl4LoWkIwS01Ig/U3pww3jzLZ3eGl8Dk3ZWJ+1YFfypHNIFeBcGAeyhTcNb9tcmSe91MauT5tsh6mE2uC26s1A/V9M7d+YHfzEfDv8DlKJ7q4l4/lFP6GGkrfN/Nd+PwMvAtyy3dX3f4PpuhG21vXZhL/xebQbXn5a72zDn9Qex29yfP+zuFjKEvJ3ZqP07V7RTKSUG8S+V4z0ardXGjIPhSvK6nhbGQ4HJqmKdXZL90fgy5rn7omuyQwNMlUmvdDw7Al80ucEFkSPDhK1ldhEaoovV+xMQt8QEiwa740/MPmy7MxX3vViOY4jjtwTDbd9Bx/aDs+Id4HbGj/CKTfXG5p6JU3ZJiDu57PNUlSjip+785I1m6RoZxhaMu2ZGmS1CcD3fDHhmopujlV+tbUdC3DNnraEDTss3sGhLU1d3OdZf0ga6Mlk+7wZvbccTw/XMIg7H++7437VnsIbBOLTjJmlVJb+SVZviTNeej1K8sPi8fR/1jXTKMJGqoOe7/Oo5nviOd580h80naoOl+GMox2ELgZHTgUsSA6gbMypB+hocfr1IO3MlwoubbxDBsGGr/ksKJ55z/8+fMwFn01zp6Bewv4sO3S0GBDdm7Y14zA9pnAxjCYa87YgQ3/kJFlzcZEZfUwLE0T+inf5AgeeycO+LeXhiMtCA2Hc+qHcZ8bw4nf1lXjB2jYC7/fgc9oli3NTPmciRq0HazAux8LQ1O+lUJDyZuaYVIXhlNe1fgGlsBQ6Zu6rgf+6yoPn5XPGf4CY+9IfFuMW4eNtWyHh31wQ1Prs/GXvjRkAq7amQemO2raUUOnzYdoUru5aIo6Y+nO45esMicmd8JIChJfUD9uFT7aChuMqcw/G/67P5AeeHHrKz41bMX3iC9r7TZdG4ZDtP+6r/JikGY1u3fhpWavfGDzltjxzlMZiwqPHhL+2GX/9Fhh0925F/7CHjOzTUm3bV0y7a6qr0cyy7vMVaXT1cWlz5yhtaNftEAnBf+0upx+ad9sMgniTpqLwR5RtMLnpes+z2xdc/Lf+h0I+o6sUP+9+Hlwvk3Oa5PzvZY8MjDZ/NYt1wWz4eEga7yEIAiCIAiCIAiCIAiCIAiCIAiCIAiCIAiCIAiCIAiCIAiCIAiCIAiCfHP+B/4tQFAdUdkbAAAAAElFTkSuQmCC" alt="">
            </div>  
            <div class="card-content">
                <h2>Qbit</h2>
                <p>Transportation management software helps businesses enhance their shipping operations. It’s part of supply chain management and allows businesses to automate tasks and save money on shipping. This is especially important for manufacturing businesses who want to streamline their shipping processes. An online good TMS platform is a valuable tool that helps them achieve these objectives.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Transportation, Logistics, Supply Chain</p>
            </div>
        </div>
        <div class="card" id='Rocket Flyer Technology Pvt. Ltd.'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Rocket Flyer Technology Pvt. Ltd.</h2>
                <p>Transport Management System by RocketFlow leverages AI to optimize transportation operations. It offers a website, a mobile app for drivers/executives, and a web admin portal for supervisors and management teams through an Enterprise SAAS Offering. Easy to use and ability to Scale the Business, Adherence to Documentation and Compliance.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Software Development, IT</p>
            </div>
        </div>
        <div class="card" id='Saddle Point'>
            <div class="card-image">
                <img src="data:image/png;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Saddle Point</h2>
                <p>Saddle Point is a supply chain solution provider offering an integrated cloud-based platform that leverages AI, machine learning, and advanced optimization algorithms. Their technology enhances visibility, provides real-time insights, and facilitates end-to-end collaboration.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Automotive, Building Materials, Consumer Goods, Facilities Services, Healthcare, Logistics, Manufacturing, Oil & Energy, Pharmaceuticals, Telecommunications</p>
            </div>
        </div>
        <div class="card" id='Shipsy'>
            <div class="card-image">
                <img src="data:image/png;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Shipsy</h2>
                <p>Shipsy is a logistics and supply chain management platform that enhances visibility and efficiency, provides predictive analytics, and seamless data integration. Utilizing technologies like AI, IoT, and cloud computing, Shipsy enables proactive decision-making and optimized operations across the entire supply chain.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: Software Development</p>
            </div>
        </div>
        <div class="card" id='Technofort (Palms)'>
            <div class="card-image">
                <img src="data:image/jpeg;base64,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" alt="">
            </div>  
            <div class="card-content">
                <h2>Technofort (Palms)</h2>
                <p>PALMS enhances visibility with real-time tracking, consolidates data by integrating with systems, and optimizes warehouse operations through features like guided picking by using AI, algorithms and ML models. They also provide integration with third-party systems, cost reduction features, and better overall inventory management for improved business intelligence.</p>
            </div>
            <div class="card-footer">
                <p>Sectors: IT Services and IT Consulting</p>
            </div>
        </div>
    </div>
<script>
    // Function to load sectors from Flask
    async function loadSectors() {
        try {
            const response = await fetch('http://localhost:3000/get-sectors');
            const data = await response.json();
            const sectorSelect = document.getElementById('sector');
            
            // Populate the dropdown with sectors
            data.sectors.forEach(sector => {
                const option = document.createElement('option');
                option.value = sector;
                option.textContent = sector;
                sectorSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error fetching sectors:', error);
        }
    }
    window.onload = loadSectors;

    let autocomplete_suggestions = []
    // Fetch suggestions from Flask backend based on sector
    async function fetchSuggestions(sector) {
        try {
            const response = await fetch(`http://localhost:3000/suggestions?sector=${encodeURIComponent(sector)}`);
            if (!response.ok) {
                throw new Error('Failed to fetch suggestions');
            }
            autocomplete_suggestions = await response.json();
            autocomplete_suggestions.sector = sector; // Store the sector with the suggestions
            showSuggestions(); // Call showSuggestions after fetching data
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            autocomplete_suggestions = []; // Reset suggestions on error
            displayErrorMessage("Failed to fetch suggestions."); // Provide user feedback
        }
    }

    function showSuggestions() {
        const input = document.getElementById('search-text');
        const suggestionBox = document.getElementById('suggestion-box');
        const sectorSelect = document.getElementById('sector'); 

        const userInput = input.value.toLowerCase();
        suggestionBox.innerHTML = ''; // Clear previous suggestions

        if (userInput === '') {
            suggestionBox.style.display = 'none';
            return;
        }

        const selectedsector = sectorSelect.value; 

        // Fetch suggestions if they are not available or if the sector has changed
        if (autocomplete_suggestions.length === 0 || autocomplete_suggestions.sector !== selectedsector) {
            fetchSuggestions(selectedsector);
        }

        // Assuming autocomplete_suggestions is populated via AJAX
        if (autocomplete_suggestions && Array.isArray(autocomplete_suggestions.suggestions)) {
            const filteredSuggestions = autocomplete_suggestions.suggestions.filter(item =>
                item.toLowerCase().includes(userInput.toLowerCase())
            );

            if (filteredSuggestions.length > 0) {
                suggestionBox.style.display = 'block';
                suggestionBox.innerHTML = ''; // Clear previous suggestions
                filteredSuggestions.forEach(suggestion => {
                    const suggestionItem = document.createElement('div');
                    suggestionItem.classList.add('suggestion-item');
                    suggestionItem.innerText = suggestion;
                    suggestionItem.onclick = () => {
                        document.getElementById('search-text').value = suggestion;
                        suggestionBox.style.display = 'none';
                    };
                    suggestionBox.appendChild(suggestionItem);
                });
            } else {
                suggestionBox.style.display = 'none';
            }
        } else {
            suggestionBox.style.display = 'none'; // Ensure suggestion box is hidden
            displayErrorMessage("Failed to fetch valid suggestions.");
        }

    }

    // Function to display error message
    function displayErrorMessage(message) {
        const errorElement = document.getElementById("error-message"); // Replace with your error element ID
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = "block"; // Show the error message
        }
    }

    function searchSoftware(event) {
        const sector = document.getElementById('sector').value;
        const searchText = document.getElementById('search-text').value;

        console.log('Sector:', sector); // Checkpoint 1: Log sector value
        console.log('Search text:', searchText); // Checkpoint 2: Log search text

        if (!sector || !searchText) {
            alert('Please fill in both fields!');
            return;
        }

        // Show loading spinner
        const loadingSpinner = document.getElementById('loading-spinner');
        loadingSpinner.style.display = 'block';

        console.log('Fetching data from backend...'); // Checkpoint 3: Before fetching data

        // Fetch data from backend
        fetch('http://localhost:3000/get_similarities', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ sector: sector, input: searchText })
        })
        .then(response => response.json()) // Checkpoint 4: Parse response
        .then(data => {
            console.log('Backend response:', data); // Log the response data

            // Hide loading spinner
            loadingSpinner.style.display = 'none';

            if (data.length > 0) {
                const searchResultsContainer = document.getElementById('search-results');
                const allCards = Array.from(searchResultsContainer.getElementsByClassName('card')); // Get all cards

                // Reset visibility of all cards
                allCards.forEach(card => {
                    card.style.display = 'none'; // Hide all cards initially
                });

                console.log('Matching Leads:', data); // Log matching leads

                // Loop through the data and display matching cards
                data.forEach(leadId => {
                    const matchingCard = allCards.find(card => card.id.trim().toLowerCase() === leadId.trim().toLowerCase());
                    if (matchingCard) {
                        matchingCard.style.display = 'block'; // Show matching card
                        searchResultsContainer.appendChild(matchingCard); // Move the card to the end of the container
                        console.log(`Card ${leadId} matches.`); // Log matching card
                    } else {
                        console.warn(`No card found for ID: ${leadId}`); // Log missing card
                    }
                });

            } else {
                alert('No matching data found!');
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error); // Log error
            loadingSpinner.style.display = 'none'; // Hide spinner on error
        });


    }











</script>

</body>
</html>
