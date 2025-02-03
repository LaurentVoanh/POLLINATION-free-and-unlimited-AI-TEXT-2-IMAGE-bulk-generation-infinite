<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diaporama Image Generator</title>
    <style>
        /* ... existing styles ... */
    </style>
</head>
<body>
    <div class="popup" id="popup">
        <input type="text" id="prompt" placeholder="Entrez votre prompt">
        <input type="number" id="imageCount" placeholder="Nombre d'images souhaitées">
        <button onclick="startProcess()">Valider</button>
    </div>
    <div class="slideshow" id="slideshow">
        <div class="slide" style="background-image: url('loading.gif');"></div>
        <div class="text">Chargement...</div>
    </div>
    <div class="debug" id="debug"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slideshow = document.getElementById('slideshow');
            const debug = document.getElementById('debug');
            let currentIndex = 0;
            let images = [];
            let texts = [];
            let prompt = '';
            let imageCount = 0;
            let currentSeed = 0;
            let savedImages = [];

            function log(message) {
                console.log(message);
                debug.innerHTML += `<p>${message}</p>`;
                debug.scrollTop = debug.scrollHeight;
            }

            function showSlide(index) {
                const slides = document.querySelectorAll('.slide');
                const textElements = document.querySelectorAll('.text');
                slides.forEach((slide, i) => {
                    if (i === index) {
                        slide.classList.add('active');
                        textElements[i].classList.add('active');
                    } else {
                        slide.classList.remove('active');
                        textElements[i].classList.remove('active');
                    }
                });
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % images.length;
                showSlide(currentIndex);
            }

            function updateSlideshow() {
                slideshow.innerHTML = '';
                images.forEach((image, index) => {
                    const slide = document.createElement('div');
                    slide.className = 'slide';
                    slide.style.backgroundImage = `url('${image}')`;
                    slideshow.appendChild(slide);

                    const text = document.createElement('div');
                    text.className = 'text';
                    text.textContent = texts[index] || `Image ${index + 1}`;
                    slideshow.appendChild(text);
                });
                showSlide(currentIndex);
            }

            async function fetchImage(seed) {
                log(`Tentative de récupération de l'image avec le seed ${seed}`);
                const url = `https://image.pollinations.ai/prompt/${encodeURIComponent(prompt)}?width=1080&height=1920&nologo=poll&nofeed=yes&seed=${seed}`;
                
                try {
                    const response = await fetch(url);
                    const blob = await response.blob();
                    const imageName = `image/${await getIP()}_${seed}_${prompt.split(' ').slice(0, 2).join('_')}.png`;
                    const jsonName = `image/${await getIP()}_${seed}_${prompt.split(' ').slice(0, 2).join('_')}.json`;

                    // Enregistrer l'image sur le serveur
                    await saveImageToServer(blob, imageName);

                    // Créer et sauvegarder le JSON sur le serveur
                    const jsonData = {
                        prompt: prompt,
                        image: imageName,
                        date: new Date().toISOString()
                    };
                    await saveJSONToServer(jsonData, jsonName);

                    // Mettre à jour le diaporama
                    const imageURL = `https://yourwebsite/${imageName}`;
                    log(`Image enregistrée : ${imageURL}`);
                    
                    images.push(imageURL);
                    texts.push(`Image ${images.length}`);
                    updateSlideshow();
                    savedImages.push({ url: imageURL, name: imageName, json: jsonName });

                    if (images.length < imageCount) {
                        fetchNextImage();
                    }
                } catch (error) {
                    log(`Erreur lors de la récupération de l'image : ${error.message}`);
                    fetchNextImage();
                }
            }

            function fetchNextImage() {
                currentSeed = Math.floor(Math.random() * (55555 - 11111 + 1)) + 11111;
                log(`Nouveau seed généré : ${currentSeed}`);
                fetchImage(currentSeed);
            }

            async function saveImageToServer(blob, imageName) {
                const formData = new FormData();
                formData.append('image', blob, imageName);
                
                try {
                    const response = await fetch('upload-image.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.text();
                    log(`Réponse du serveur pour l'image : ${result}`);
                } catch (error) {
                    log(`Erreur lors de l'envoi de l'image : ${error.message}`);
                }
            }

            async function saveJSONToServer(jsonData, jsonName) {
                try {
                    const response = await fetch('upload-json.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ 
                            json: jsonData, 
                            filename: jsonName 
                        })
                    });
                    const result = await response.text();
                    log(`Réponse du serveur pour le JSON : ${result}`);
                } catch (error) {
                    log(`Erreur lors de l'envoi du JSON : ${error.message}`);
                }
            }

            async function getIP() {
                try {
                    const response = await fetch('https://api.ipify.org?format=json');
                    const data = await response.json();
                    return data.ip.replace(/\./g, '_');
                } catch (error) {
                    log(`Erreur lors de la récupération de l'IP : ${error.message}`);
                    return 'unknown';
                }
            }

            window.startProcess = function() {
                prompt = document.getElementById('prompt').value;
                imageCount = parseInt(document.getElementById('imageCount').value);
                log(`Prompt saisi : ${prompt}`);
                log(`Nombre d'images souhaitées : ${imageCount}`);
                document.getElementById('popup').style.display = 'none';
                fetchNextImage();
                setInterval(nextSlide, 30000); // Change slide every 30 seconds
            };
        });
    </script>
</body>
</html>
