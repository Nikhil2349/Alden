<html lang="en-US" prefix="og: https://ogp.me/ns#">
   <head>
<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
 <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=10.0, user-scalable=yes">
<!-- jQuery UI library -->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
   <link media="all" href="style.css" rel="stylesheet" />
   <link rel="stylesheet" href="style.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

<script>
        let sectors = []; 

        async function loadSectors() {
            try {
                const response = await fetch('http://localhost:3000/get-sectors');
                const data = await response.json();
                sectors = data.sectors; // Store fetched sectors
            } catch (error) {
                console.error('Error fetching sectors:', error);
            }
        }

        function filterSectors() {
            const input = document.getElementById('sector').value.toLowerCase();
            const sectorList = document.getElementById('sector-list');
            const sectorField = document.getElementById('sector');

            sectorList.innerHTML = ''; 

            if (input) {
                const filteredSectors = sectors.filter(sector =>
                    sector.toLowerCase().startsWith(input)
                );

                sectorList.style.position = 'absolute';
                sectorList.style.top = `${sectorField.offsetTop + sectorField.offsetHeight}px`;
                sectorList.style.left = `${sectorField.offsetLeft}px`;
                sectorList.style.width = `${sectorField.offsetWidth}px`; 

                filteredSectors.forEach(sector => {
                    const listItem = document.createElement('li');
                    listItem.textContent = sector;
                    listItem.style.padding = '5px';
                    listItem.style.borderBottom = '1px solid #ddd';
                    listItem.style.cursor = 'pointer';
                    listItem.onclick = () => {
                        document.getElementById('sector').value = sector;
                        sectorList.style.display = 'none'; 
                    };
                    sectorList.appendChild(listItem);
                });

                sectorList.style.display = filteredSectors.length ? 'block' : 'none';
            } else {
                sectorList.style.display = 'none'; 
            }
        }

        window.onload = loadSectors;

        let autocomplete_suggestions = [];

        async function fetchSuggestions(sector) {
            try {
                const response = await fetch(`http://localhost:3000/suggestions?sector=${encodeURIComponent(sector)}`);
                if (!response.ok) {
                    throw new Error('Failed to fetch suggestions');
                }
                autocomplete_suggestions = await response.json();
                autocomplete_suggestions.sector = sector;
                showSuggestions(); 
            } catch (error) {
                console.error('Error fetching suggestions:', error);
                autocomplete_suggestions = []; 
                displayErrorMessage("Failed to fetch suggestions."); 
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('search-text');
            if (input) {
                input.addEventListener('focus', showSuggestions);
            }
        });

        function showSuggestions() {
            const input = document.getElementById('search-text');
            const suggestionBox = document.getElementById('suggestion-box');
            const sectorSelect = document.getElementById('sector');
            const userInput = input.value.toLowerCase();
            suggestionBox.innerHTML = ''; 

            if (userInput === '') {
                suggestionBox.style.display = 'none';
                return;
            }

            const selectedsector = sectorSelect.value;

            if (autocomplete_suggestions.length === 0 || autocomplete_suggestions.sector !== selectedsector) {
                fetchSuggestions(selectedsector);
            }

            if (autocomplete_suggestions && Array.isArray(autocomplete_suggestions.suggestions)) {
                const filteredSuggestions = autocomplete_suggestions.suggestions
                .filter(item => item.toLowerCase().includes(userInput))
                .slice(0, 5);

                if (filteredSuggestions.length > 0) {
                    suggestionBox.style.display = 'block';
                    suggestionBox.innerHTML = ''; 

                    filteredSuggestions.forEach(suggestion => {
                        const suggestionItem = document.createElement('div');
                        suggestionItem.classList.add('suggestion-item');
                        suggestionItem.innerText = suggestion;
                        suggestionItem.onclick = () => {
                            input.value = suggestion;
                            suggestionBox.style.display = 'none';
                            searchSoftware();
                        };
                        suggestionBox.appendChild(suggestionItem);
                    });
                } else {
                    suggestionBox.style.display = 'none';
                }
            } else {
                suggestionBox.style.display = 'none';
                displayErrorMessage("Failed to fetch valid suggestions.");
            }
        }

        document.getElementById('search-text').addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault(); 
                searchSoftware(); 
            }
        });

        function displayErrorMessage(message) {
            const errorElement = document.getElementById("error-message");
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = "block"; 
            }
        }

        function searchSoftware(event) {
            const sector = document.getElementById('sector').value;
            const searchText = document.getElementById('search-text').value;
            const searchResultsContainer = document.getElementById('search-results');
            const allCards = Array.from(searchResultsContainer.getElementsByClassName('card')); 

            // Input validation
            if (!sector || !searchText) {
                alert('Please fill in both fields!');
                return;
            }

            const loadingSpinner = document.getElementById('loading-spinner');

            // Display the loading spinner and hide all cards
            loadingSpinner.style.display = 'block';
            allCards.forEach(card => {
                card.style.display = 'none'; // Hide all cards
            });

            // Fetch data from the backend
            fetch('http://localhost:3000/get_similarities', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ sector: sector, input: searchText })
            })
            .then(response => response.json())
            .then(data => {
                loadingSpinner.style.display = 'none'; // Hide loading spinner
                if (data.length > 0) {
                    // Show matching cards
                    data.forEach(leadId => {
                        const matchingCard = allCards.find(card => card.id.trim().toLowerCase() === leadId.trim().toLowerCase());
                        if (matchingCard) {
                            matchingCard.style.display = 'block'; // Display matching card
                            searchResultsContainer.appendChild(matchingCard); // Reorder the matching card
                        } else {
                            console.warn(`No card found for ID: ${leadId}`);
                        }
                    });
                } else {
                    alert('No matching data found!');
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                loadingSpinner.style.display = 'none'; // Ensure spinner is hidden on error
            });
        }

        function navigateTo(url) {
            window.location.href = url;
        }
</script>

<style>
.fixed-height {
			padding: 1px;
			max-height: 200px;
			overflow: auto;
		}
#searchForm:hover{
    box-shadow:none;
}
#search-text{
    width:400px ;
    
    height: 20px !important;
    padding: 20px !important;
      
    border-radius: 30px;
}
#tag{
      width:400px;
    
    height: 20px !important;
    padding: 20px !important;
      
    border-radius: 30px;
}
.srch{
       margin-left: -52px;
    background: none;
    background: blue;
    min-height: 40px !important;
    border-top-right-radius: 20px !important;
    border-bottom-right-radius: 20px !important;
    color:#fff;
}
.imd-mid{
    width: 483px;margin: 0 auto;margin-top: 50px;
    
}
.s-mid{
    width: 144px;
    margin: 0 auto;
}
.get1{
        padding: 12px 27px;
       margin-top: 30px;
   
    color: #fff;
    border-radius: 50px;
        background: #564FCC;
    color: #fff;
    display: inline-block;
    font-weight: bold;
    text-decoration: none;
    font-size: 20px;
}
.section-abt {
    clear: both;
 padding:20px 0 20px 0;
    width: 1080px;
    margin: 0 auto;
    min-height: 450px;
    color: #000;
}
.abt-rt {
    width: 600px;
    float: left;
}
.abt-lt {
    width: 420px;
    float: left;
    margin-right: 30px;
}
.banner{
    background:url('image/ai-bnr.jpg');
        width: 1220px;
    min-height: 500px;
    background-size:100% 100%;
       
}
 #sector{
         width: 400px ;
    height: 20px !important;
    padding: 20px !important;
      
    border-radius: 30px;
 }   
 .btc{
         min-height: 210px;
    padding: 20px;
 }
 .ai-mid{
     width:720px;
     margin:0 auto;
     padding:60px;
 }
 .insights{
     min-height:230px;background:#F4F4F4;padding: 20px; margin-top:20px;
 }
 .slk{
     font-size:36px;
     text-align: center;
     line-height:75px;
     
 }
 
 .shipsy{
     width: 235px;float: left;margin-left:40px;border: 1px solid lightgrey;height: 150px;
   
 }
 .mps{
     height:140px;
 }
 .lg{
     height:180px;
 }
 .btl{
        width: 195px;
    float: left;
    margin-right: 20px;
    margin-left: 20px;
 }
 .indv{
     width: 590px;margin-bottom: 30px;
 }
  .btc h1{
         font-size: 36px;
    text-align: center;
    padding:30px;
     }
     .mps h1{
          font-size: 36px;
    text-align: center;
    padding:30px;
         
     }
     .btl1{
             width: 202px;
    float: left;
     }
      .fa-search{
          margin-left: -52px;
    background: #ea1286;
    min-height: 40px !important;
    border-top-right-radius: 20px !important;
    border-bottom-right-radius: 20px !important;
    color: #fff;
    width: 47px;
    padding: 12px;
    
         
     }
      .fa-s{
          margin-left: -52px;
    background: #564FCC;
    min-height: 40px !important;
    border-top-right-radius: 20px !important;
    border-bottom-right-radius: 20px !important;
    color: #fff;
    width: 47px;
    padding: 12px;
         
     }
     
 @media screen and (max-width: 960px) {
     #skill_input{
                       width: 230px;
        height: 20px !important;
        padding: 16px !important;
     }
     #searchForm{
       background:none;  
     }
     .fa-s{ padding: 7px !important;
     min-height: 31px !important;
         
     }
      .fa-search{padding: 8px !important;
     min-height: 31px !important;
         
     }
     .s-mid{
                width: 83%;
        margin: 0 auto;
        padding: 10px;
    
     }
     .s-mid img{
             padding-top: 30px;
    text-align: center;
     }
     .imd-mid{width:100%;
         margin-top: 0px;
                 text-align: center;
     }
     
     .abt-rt h3{
         font-size:16px;
     }
     .cool-links{
             width: 230px;
    display: inline-block;
    margin-bottom: 20px;
        padding: 0px 35px !important;
     }
      .cool-link1{
             width: 180px;
    display: inline-block;
    margin-bottom: 10px !important;
                padding: 7px 0px !important;
            text-align: center;
     }
   .shipsy{
          width: 260px !important;
        margin: 0 auto;
        text-align: center;
        float: none;
        margin-bottom: 30px;
       
        height: 180px;
   }
     .btl1{
             width: 100%;
    margin: 0 auto;
    text-align: center;
     }
     .btc1{
             width: 100%;
     }
     
     .btc{
         width: 100%;
    height: 440px;

     }
     .btc h1{
        font-size: 26px !important;
        padding: 0 0 30px 0 !important;
        margin: 0;
     }
      .mps h1{
         font-size: 24px !important;
    padding:0px !important;
     }
    
     .btl{
                     width: 100%;
        margin: 0 auto;
        text-align: center;
     }
     .slk{
             font-size: 31px;
    text-align: center;
    line-height: 42px;
     }
     .indv{display:none;
         width: 100% ;
     }
     .abt-rt, .abt-lt {width: 100% ;
     }
     .txt1{
         width: 100% !important;
     }
    
     .section-abt{        width: 100%;
        padding: 20px;
        height: 690px;
         
     }
      .section-abt h1
      { 
          font-size:24px !important;
          
      }
      .abt-rt h2{font-size:22px !important;
          
      }
     .bd{
         display:none;
     }
     .imd-mid h3{
         font-size: 16px;
    padding: 10px 20px 10px 20px;
    text-align: center;
     }
     .banner{
                background-size: 100% 100%;
          width: 100% ;
          height:auto !important;
          min-height:0 !important;
     }
     #sector{
                    width: 243px !important;
        height: 20px !important;
        padding: 16px !important;
        margin-left: -13px;
     }
     .ai-mid{
           width: 100% ;
           padding:10px !important;
     }
    .container {
        width: 100% !important;
                margin-top: 13px;
    }
    .get1{
        font-size:16px;
            padding: 10px 18px;
    }
    
}
        .card {
            display:None;
            padding: 5px;
            gap: 10px; 
            width: 380px;
            min-height: 10px; 
            margin-bottom: 2px;
            cursor:pointer;
        }

        .card-content img {
            border: 1px solid #9b9999;
            width: 60px; 
            height: 50px; 
            object-fit: contain; 
            border-radius: 5px; 
        }

        .card h2 {
            font-size: 15px; 
            font-weight: bold; 
            margin: 0; 
        }

        .card-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        #sector-list {
            overflow-y: auto;
            position: absolute; /* Change to absolute */
            top: 100%; /* Adjusts position relative to the parent */
            left: 0;
            width: 400px;
            border: 1px solid #ccc;
            background: white;
            max-height: 1000px;
            list-style: none;
            margin: 0;
            padding: 0;
            display: none;
            z-index: 1000;
        }

        #suggestion-box {
            border: 1px solid #ccc;
            top: 70%;
            overflow: auto;
            position: absolute;
            background-color: #fff;
            width: 400px;
            z-index: 1;
            display: none;
            border-radius: 5px;
            padding: 5px;
            z-index: 9999;
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
        #search-results{
            overflow: auto;
            position: absolute;
            background: #fff;
            border-radius:15px;
            padding:10px;
        }
</style>
</head>
<body>
<div >
    <?php  include 'menu-d.php'; ?>
    <div class="main container">
    <div class="banner ">
<form method="post" action="" id="conf-reg">
    <!-- Autocomplete input field -->
    <div class="ai-mid">
    <div class="form-group">
        
        
        <div  class="indv" style="display:none;">
         <a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#564FCC;color:#fff;">Logistics</a><a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#eceaea;color:#000;">Warehouse</a><a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#eceaea;color:#000;">Supply chain</a>
         </div>
         
         <div class="imd-mid" style="">
          <h3>Which industry you belong to?</h3>
        <input type="text" id="sector" name="skill_input"  class="" oninput="filterSectors()" placeholder="Start typing..." >
        <ul id="sector-list"></ul>

       
        <label><h3 style="" >What Solutions are you <span style="color:#aa4e14;">Looking  for?</span></h3></label>
        <input type="text" id="search-text" name="skill_input"  class="" required oninput="showSuggestions()" placeholder="Start typing...">
        <div id="suggestion-box" style="border: 1px solid #ccc; display: none;"></div>
        <div id="search-results">
                <div class="card" id="Brainvire" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/brainvire.php')">
                    <div class="card-content">
                        <img src="image/brainwire.svg" alt="">
                        <h2>Brainvire</h2>
                    </div>
                </div>
                <div class="card" id="Cogoport" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/cogoport.php')">
                    <div class="card-content">
                        <img src="https://thealdenglobal.com/ai-segregation/image/logo-cogoport-1.svg" alt="">
                        <h2>Cogoport</h2>
                    </div>
                </div>

                <div class="card" id="Crest" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/crest.php')">
                    <div class="card-content">
                        <img src="image\crest.png" alt="">
                        <h2>Crest</h2>
                    </div>
                </div>
                <div class="card" id="Datalabs India Solutions Private Limited" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/datalabs.php')">
                    <div class="card-content">
                        <img src="image\datalabs.png" alt="">
                        <h2>Datalabs India Solutions Private Limited</h2>
                    </div>
                </div>
                <div class="card" id="Elixia Tech" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/elixiatech.php')">
                    <div class="card-content">
                        <img src="image/el.png" alt="">
                        <h2>Elixia Tech</h2>
                    </div>
                </div>
                <div class="card" id="Inciflo" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/inflico.php')">
                    <div class="card-content">
                        <img src="image\inflico.avif" alt="">
                        <h2>Inciflo</h2>
                    </div>
                </div>
                <div class="card" id="Inquizity" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/intiquzity.php')">
                    <div class="card-content">
                        <img src="image\inti.png" alt="">
                        <h2>Inquizity</h2>
                    </div>
                </div>
                <div class="card" id="Intugine Technologies" onclick="navigateTo('http://thealdenglobal.com/ai-segregation/intiguine.php')">
                    <div class="card-content">
                        <img src="image\intiguine.png" alt="">
                        <h2>Intugine Technologies</h2>
                    </div>
                </div>
                <div class="card" id="Lepton Software" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/lepton.php')">
                    <div class="card-content">
                        <img src="image\lptn.png" alt="">
                        <h2>Lepton Software</h2>
                    </div>
                </div>
                <div class="card" id="NEWAGENXT" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/newage.php')">
                    <div class="card-content">
                        <img src="image\newage.jpg" alt="">
                        <h2>NEWAGENXT</h2>
                    </div>
                </div>
                <div class="card" id="ORMAE" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/ormae.php')">
                    <div class="card-content">
                        <img src="image\ORMAE Logo.png" alt="">
                        <h2>ORMAE</h2>
                    </div>
                </div>
                <div class="card" id="Qbit" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/qbit.php')">
                    <div class="card-content">
                        <img src="image\qbit.png" alt="">
                        <h2>Qbit</h2>
                    </div>
                </div>
                <div class="card" id="Rocket Flyer Technology Pvt. Ltd." onclick="navigateTo('https://thealdenglobal.com/ai-segregation/rocketflyer.php')">
                    <div class="card-content">
                        <img src="image\rocket.jpeg" alt="">
                        <h2>Rocket Flyer Technology Pvt. Ltd.</h2>
                    </div>
                </div>
                <div class="card" id="Saddle Point" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/saddle-point.php')">
                    <div class="card-content">
                        <img src="image\saddle.jpeg" alt="">
                        <h2>Saddle Point</h2>
                    </div>
                </div>
                <div class="card" id="Shipsy" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/shipsy.php')">
                    <div class="card-content">
                        <img src="image\Shipsy_Logo.jpg" alt="">
                        <h2>Shipsy</h2>
                    </div>
                </div>
                <div class="card" id="Palms" onclick="navigateTo('https://thealdenglobal.com/ai-segregation/onplams.php')">
                    <div class="card-content">
                        <img src="image\palms-logo.jpg" alt="">
                        <h2>Palms</h2>
                    </div>
                </div>
        </div>    
        <div id="loading-spinner" style="display: none;">...</div>

</div>
    
     
    </div>
 <div class="form-group">
    <!-- Submit button -->
    <br/>
    </div>
</form>

</div>



</div>






 <div class="btc"  style="display:none;">
<h1>Browse from top Categories</h1>
  <div class="btl"><a href="https://thealdenglobal.com/contact-us/" class="cool-links"><img src="image/lgs.png" /> Logistics</a></div>
   <div class="btl"> <a href="https://thealdenglobal.com/contact-us/" class="cool-links"><img src="image/icons8-supply-chain-64.png" width="40" />Supply Chain</a></div>
    <div class="btl">  <a href="https://thealdenglobal.com/contact-us/" class="cool-links"><img src="image/icons8-inventory-48.png" />Inventory</a></div>
      <div class="btl">  <a href="https://thealdenglobal.com/contact-us/" class="cool-links"><img src="image/icons8-track-order-48.png" width="40" />Track & Trace</a></div>
       <div class="btl">  <a href="https://thealdenglobal.com/contact-us/" class="cool-links"><img src="image/icons8-module-48.png" />Freight Module</a></div>
       
          </div>
          
          <div class="mps">
            <h1 >Most Popular Solutions</h1>
             <div class="btc1" >
               <div class="btl1">  <a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#564FCC;color:#fff;">All Categories</a></div>
     <div class="btl1"><a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#eceaea;color:#000;">Supply Chain</a></div>
     <div class="btl1"> <a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#eceaea;color:#000;">Inventory</a></div>
      <div class="btl1">  <a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#eceaea;color:#000;">Warehouse</a></div>
       <div class="btl1">  <a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#eceaea;color:#000;">Track & Trace</a></div>
        <div class="btl1">  <a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#eceaea;color:#000;">More</a></div>
              </div>
          </div>
          <p>&nbsp;</p>
          <div class="shipsy">
            <div class="s-mid">
               <img src="image/el.png" width="" style="width: 205px;padding-top: 30px;"/><br/>
               <a href="https://thealdenglobal.com/ai-segregation/elixiatech.php" class="cool-link1" style="margin-top: 30px;background:#77B747;color:#fff;display: inline-block;padding: 7px 25px;">Read More</a>
              </div>
          </div>
          <div class="shipsy" >
              <div class="s-mid">
               <img src="image/inti.png" width="150" style="padding-top: 26px;padding-bottom: 10px;"/><br/>
               
               <a href="https://thealdenglobal.com/ai-segregation/intiquzity.php" class="cool-link1" style="margin-top: 10px;background:#77B747;color:#fff;display: inline-block;padding: 7px 25px;">Read More</a>
               </div>
              
          </div>
          <div class="shipsy" >
              <div class="s-mid">
               <img src="image/palms-logo.jpg" width="150" style="padding: 20px 20px 10px 20px;"/><br/>
               <a href="https://thealdenglobal.com/ai-segregation/onplams.php" class="cool-link1" style="margin-top: 20px;background:#77B747;color:#fff;display: inline-block;padding: 7px 25px;">Read More</a>
              </div>
          </div>
           <div class="shipsy" >
              <div class="s-mid">
               <img src="image\saddle.jpeg" width="150" style="padding: 20px 20px 10px 20px;"/><br/>
               <a href="https://thealdenglobal.com/ai-segregation/saddle-point.php" class="cool-link1" style="background:#77B747;color:#fff;display: inline-block;padding: 7px 25px;">Read More</a>
              </div>
          </div>
          <div style="width:200px;margin:0 auto;display:none;">
 <a href="https://thealdenglobal.com/contact-us/" class="cool-link1" style="background:#564FCC;color:#fff;display: inline-block;">Load More</a>
 </div>
 
 <div class="insights" style="display:none;">
     
     <div class="txt" style="    width: 310px;
    height: 150px;
    float: left;
    background: none;
    padding: 19px;
    margin: 0 50px;" >
       <h1> Insights and Performance Metrics </h1>
         
     </div>
     
     <div class="txt1" style="    width: 202px;
    float: left;
    background: #000;
    height: 140px;
    padding: 15px;
    margin-top: 20px;border-radius: 15px;margin-right: 30px;" >
         <div style="text-align:center; color:#fff;">
            <h3 style="text-align:center;">5000+</h3>
            <h4 style="text-align:center;">Total Listings in the System</h4></div>
     
         
     </div>
     
     <div class="txt1" style="    width: 202px;
    float: left;
    background: #000;
    height: 140px;
    padding: 15px;
    margin-top: 20px;border-radius: 15px;margin-right: 30px;" >
         <div style="text-align:center; color:#fff;">
            <h3 style="text-align:center;">1000+</h3>
            <h4 style="text-align:center;">Active Listings in the System</h4></div>
     
         
     </div>
     <div class="txt1" style="    width: 202px;
    float: left;
    background: #000;
    height: 140px;
    padding: 15px;
    margin-top: 20px;border-radius: 15px;" >
         <div style="text-align:center; color:#fff;">
            <h3 style="text-align:center;">100+</h3>
            <h4 style="text-align:center;">Articles in the Blog</h4></div>
     
         
     </div>
     
     
     
     
 </div>
 
 <div class="section-abt  ">
      <h1 style="font-size:36px;text-align: center;padding:15px">OFFERING SOFTWARES</h1>
     
     
     <div class="abt-lt"> <img decoding="async" src="image/get-lst.png" width="400" height="280" data-src="https://www.maritzglobalevents.com/wp-content/uploads/sites/4/2023/01/manage-everything.jpg" class="wpex-align-middle wpex-w-100 lazy loaded" alt="about"></div><div class="abt-rt"><h2 style="color:#000;">Offering software?
Get noticed!</h2><p><h3 style="font-weight: 100;color: grey;">Your future customers are already looking for solutions on Alden.
Make sure your product stands out!</h3></p>



 <style>
       
        .btn-open-popup {
            padding: 12px 24px;
            font-size: 18px;
            background-color: green;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-open-popup:hover {
            background-color: #4caf50;
        }

        .overlay-container {
            display: none;
            position: fixed;
            top: 40px;
            right: 200px;
            width: 100%;
            height: 100%;
            
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .popup-box {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
            width: 320px;
            text-align: center;
            opacity: 0;
            transform: scale(0.8);
            animation: fadeInUp 0.5s ease-out forwards;
            float:right;
        }

        .form-container {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            margin-bottom: 10px;
            font-size: 16px;
            color: #444;
            text-align: left;
        }

        .form-input {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-submit,
        .btn-close-popup {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-submit {
            background-color: green;
            color: #fff;
        }

        .btn-close-popup {
            margin-top: 12px;
            background-color: #e74c3c;
            color: #fff;
        }

        .btn-submit:hover,
        .btn-close-popup:hover {
            background-color: #4caf50;
        }

        /* Keyframes for fadeInUp animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animation for popup */
        .overlay-container.show {
            display: flex;
            opacity: 1;
        }
    </style>


    <button class=""  style="background:#564FCC;color:#fff;display: inline-block;" onclick="togglePopup()">
     Get Your Solution Listed!
      </button>

    <div id="popupOverlay" 
         class="overlay-container">
        <div class="popup-box">
            <h2 style="color: green;">Query Form</h2>
            <form class="form-container">
                <label class="form-label" 
                       for="name">
                  Username:
                  </label>
                <input class="form-input" type="text" 
                       placeholder="Enter Your Username" 
                       id="name" name="name" required>

                <label class="form-label" for="email">Email:</label>
                <input class="form-input"
                       type="email" 
                       placeholder="Enter Your Email"
                       id="email" 
                       name="email" required>
                      
                      

                <button class="btn-submit" 
                        type="submit">
                  Submit
                  </button>
            </form>

            <button class="btn-close-popup" 
                    onclick="togglePopup()">
              Close
              </button>
        </div>
    </div>

    <script>
        function togglePopup() {
            const overlay = document.getElementById('popupOverlay');
            overlay.classList.toggle('show');
        }
    </script>





</div></div>


<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
 $servername = "localhost";
$username = "i5907546_wp2";
$password = "D.ePMYg87QGkTyYnuo932";
$dbname = "aldenglobal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
 

// Fetch matched data from the database 
$query = $conn->query("SELECT solutionid, solution_name FROM `ai_solutions` "); 
$query1 = $conn->query("SELECT sectorid, sector_name FROM `ai_sectors` "); 
// Generate array with soltions data 
$solutionData = []; 
if($query->num_rows > 0){ 
    while($row = $query->fetch_assoc()){ 
        $solutionData[] = [
            'id' => $row['solutionid'], 
            'value' => $row['solution_name']
        ];
    } 
    $jsonSolutionData = json_encode($solutionData);
  
} 

$sectorData = []; 
if($query->num_rows > 0){ 
    while($row1 = $query1->fetch_assoc()){ 
       $sectorData[] = [
            'id' => $row1['sectorid'], 
            'value' => $row1['sector_name']
        ];
    } 
    
    $jsonSectorData = json_encode($sectorData);
   
} 
?>
<input type="hidden" value="<?php echo htmlspecialchars($jsonSolutionData);  ?>" id="solutions" />
<input type="hidden" value="<?php echo htmlspecialchars($jsonSectorData);  ?>" id="sectors" />
</div>

   <footer class="site-footer" id="site-footer">
            <div  class="footer-main footer--row layout-full-contained"  id="cb-row--footer-main"  data-row-id="main"  data-show-on="desktop">
               <div class="footer--row-inner footer-main-inner dark-mode">
                  <div class="customify-container">
                     <div class="customify-grid  customify-grid-top">
                        <div class="customify-col-2_md-2_sm-6_xs-12 builder-item builder-first--footer-1" data-push-left="_sm-0">
                           <div class="item--inner builder-item--footer-1" data-section="sidebar-widgets-footer-1" data-item-id="footer-1" >
                              <div class="widget-area">
                                 <section id="custom_html-2" class="widget_text widget widget_custom_html">
                                    <div class="textwidget custom-html-widget">
                                       <img decoding="async" class="alignnone size-medium wp-image-1141" src="https://sp-ao.shortpixel.ai/client/to_webp,q_glossy,ret_img,w_300,h_94/https://thealdenglobal.com/wp-content/uploads/2020/01/alden-white-logo.png" alt="" width="300" height="94" />
                                       <div><i class="fa fa-home" style="font-size: 24px;"></i> 5th Floor, Tower-C, Unitech Cyber Park, Sector -39, Gurgaon, Haryana – 122003, India</div>
                                       <div style="margin-top: 10px;"><a href="tel:9910824306"><i class="fa fa-mobile" style="font-size: 24px;"></i> +91 9910824306</a></div>
                                       <div style="margin-top: 10px; width: 214px;"><a style="color: #fff;" href="mailto:info@thealdenglobal.com"><i class="fa fa-envelope-o" style="float: left;"></i><span style="float: left; display: inline-block; margin-left: 8px;">info@thealdenglobal.com</span></a></div>
                                    </div>
                                 </section>
                              </div>
                           </div>
                        </div>
                        <div class="customify-col-2_md-2_sm-6_xs-12 builder-item builder-first--footer-6" data-push-left="off-1 _sm-0">
                           <div class="item--inner builder-item--footer-6" data-section="sidebar-widgets-footer-6" data-item-id="footer-6" >
                              <div class="widget-area">
                                 <section id="custom_html-4" class="widget_text widget widget_custom_html">
                                    <div class="textwidget custom-html-widget">
                                       <div class="icons" style="width: 25px; margin: 205px 269px 0 268px;">
                                          <div><a href="https://www.linkedin.com/company/alden-global-value-advisors-pvt-ltd/"><i class="fa fa-linkedin" style="font-size: 24px;"></i></a></div>
                                       </div>
                                    </div>
                                 </section>
                              </div>
                           </div>
                        </div>
                        <div class="customify-col-3_md-3_sm-6_xs-12 builder-item builder-first--footer-4" data-push-left="off-1 _sm-0">
                           <div class="item--inner builder-item--footer-4" data-section="sidebar-widgets-footer-4" data-item-id="footer-4" >
                              <div class="widget-area">
                                 <section id="custom_html-3" class="widget_text widget widget_custom_html">
                                    <div class="textwidget custom-html-widget">
                                       <div id="comp-jpaqo2dz" class="txtNew" data-packed="false" data-min-height="17">
                                          <h6 class="font_9" style="color: #fff;"><strong>LATEST LINKS</strong></h6>
                                       </div>
                                       &nbsp;
                                       <div> <a style="text-decoration: underline;font-size:16px;font-weight:400;" href="/inflection-conference-awards/">Inflection Conference Awards</a></div>
                                       <div> <a style="text-decoration: underline;font-size:16px;font-weight:400;" href="/inflection-technology-awards/">Inflection Technology Awards</a></div>
                                       
                                       <div> <a style="text-decoration: underline;font-size:16px;font-weight:400;" href="/blog/" target="_self" rel="undefined noopener" data-content="/blog/" data-type="external">Blogs</a></div>
                                    </div>
                                 </section>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div  class="footer-bottom footer--row layout-full-contained"  id="cb-row--footer-bottom"  data-row-id="bottom"  data-show-on="desktop">
               <div class="footer--row-inner footer-bottom-inner dark-mode">
                  <div class="customify-container">
                     <div class="customify-grid  customify-grid-top">
                        <div class="customify-col-12_md-12_sm-12_xs-12 builder-item builder-first--footer_copyright" data-push-left="_sm-0">
                           <div class="item--inner builder-item--footer_copyright" data-section="footer_copyright" data-item-id="footer_copyright" >
                              <div class="builder-footer-copyright-item footer-copyright">
                                 <p>Copyright &copy; 2024  Alden Global</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </footer>
 
 </body>
 </html>
