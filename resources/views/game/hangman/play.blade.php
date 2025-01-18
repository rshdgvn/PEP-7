<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hangman Platformer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .text-gold {
            color: #F4D03F;
        }
        .bg-header-gradient {
            background: linear-gradient(to right, #1e1e1e, #3b2f2f);
        }
        .bg-footer-gradient {
            background: linear-gradient(to left, #3b2f2f, #1e1e1e);
        }
        .bg-body-gradient {
            background: linear-gradient(to bottom, #5c4033, #3b2f2f);
        }
        .text-cream {
            color: #F8F1E8;
        }
        .btn-gradient {
            background: linear-gradient(to right, #F4D03F, #8B5E3C);
            box-shadow: 0 0 10px rgba(244, 208, 63, 0.6);
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }
        .btn-gradient:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(244, 208, 63, 0.9);
        }
        body {
            font-family: 'Cinzel', serif;
            background: linear-gradient(to bottom, #5c4033, #3b2f2f);
            color: #F8F1E8;
            text-align: center;
            min-height: 100vh;
        }

        #game-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }

        canvas {
            width: 100%;
            height: auto;
            border: 2px solid #F4D03F;
            background: linear-gradient(to bottom, #8B5E3C, #5C4033);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(244, 208, 63, 0.6);
        }

        #alphabet-container .letter {
            display: inline-block;
            margin: 5px;
            padding: 10px 15px;
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(to bottom, #F4D03F, #8B5E3C);
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        #alphabet-container .letter:hover {
            transform: scale(1.1);
            box-shadow: 0 0 10px rgba(244, 208, 63, 0.9);
        }

        #alphabet-container .letter.disabled {
            pointer-events: none;
            background: #3b2f2f;
            color: #ddd;
        }

        #lives, #score {
            margin: 1rem 0;
            font-size: 18px;
        }

        .btn-gradient {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            border: none;
            background: linear-gradient(to right, #F4D03F, #8B5E3C);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(244, 208, 63, 0.6);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-gradient:hover {
            transform: scale(1.1);
            box-shadow: 0 10px 20px rgba(244, 208, 63, 0.9);
        }

        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
        }

        .popup #next-btn {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="absolute top-4 left-4 fade-in">
        <button onclick="window.location='{{ route('dashboard') }}'"
            class="btn-gradient text-dark py-2 px-4 rounded-full shadow-lg flex items-center space-x-2 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M10 19l-7-7 7-7v14zm4-14h8v14h-8V5z"/>
            </svg>
            <span>Back</span>
        </button>
    </div>
    
    <header class="bg-header-gradient py-4 fade-in">
        <h1 class="text-gold text-5xl font-extrabold uppercase tracking-wide">Hangman Platformer</h1>
        <p class="text-cream text-lg italic mt-2">Category: {{ $category }} | Level: {{ $level }}</p>
    </header>
    
    <div id="game-container" class="fade-in">
        <canvas id="game-canvas" class="fade-in"></canvas>
        <div id="description-container" class="text-lg mt-4 fade-in"></div>
        <div id="word-container" class="text-2xl font-bold tracking-widest fade-in"></div>
        <div id="alphabet-container" class="fade-in"></div>
    </div>

    <div id="lives" class="mt-4 fade-in">Lives: 10</div>
    <div id="score" class="mt-2 fade-in">Score: {{ $player->scores->hangman_score }}</div>

    <footer class="bg-footer-gradient py-4 mt-8">
        <p class="text-gold fade-in">&copy; 2025 Hangman Game. Designed with passion and fun.</p>
    </footer>

    <div id="popup" class="popup">
        <div id="popup-content"></div>
        <button id="next-btn" class="btn-gradient">Next Level</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const levels = {
                easy: {
                    1: { word: 'cat', description: 'A small domesticated carnivorous mammal.' },
                    2: { word: 'dog', description: 'A domesticated carnivorous mammal that barks.' },
                    3: { word: 'bat', description: 'A flying mammal that uses echolocation.' },
                    4: { word: 'rat', description: 'A medium-sized rodent.' },
                    5: { word: 'bee', description: 'An insect known for producing honey.' },
                    6: { word: 'cow', description: 'A large domesticated bovine animal.' },
                    7: { word: 'pig', description: 'A domesticated swine often kept for meat.' },
                    8: { word: 'hen', description: 'A female chicken.' },
                    9: { word: 'owl', description: 'A nocturnal bird of prey with a flat face.' },
                    10: { word: 'fox', description: 'A small, wild, omnivorous mammal.' }
                },
                medium: {
                    1: { word: 'apple', description: 'A sweet, edible fruit from the apple tree.' },
                    2: { word: 'grape', description: 'A small, sweet fruit used to make wine.' },
                    3: { word: 'peach', description: 'A soft, juicy fruit with a fuzzy skin.' },
                    4: { word: 'mango', description: 'A tropical fruit with sweet orange flesh.' },
                    5: { word: 'lemon', description: 'A yellow citrus fruit known for its sour taste.' },
                    6: { word: 'melon', description: 'A large fruit with a sweet, juicy flesh.' },
                    7: { word: 'cherry', description: 'A small, round fruit with a pit, often red or black.' },
                    8: { word: 'banana', description: 'A long, curved fruit with soft flesh inside.' },
                    9: { word: 'papaya', description: 'A tropical fruit with orange flesh and black seeds.' },
                    10: { word: 'guava', description: 'A tropical fruit with green skin and pink or white flesh.' }
                },
                hard: {
                    1: { word: 'elephant', description: 'A large mammal with a trunk.' },
                    2: { word: 'dinosaur', description: 'A diverse group of extinct reptiles.' },
                    3: { word: 'kangaroo', description: 'A marsupial native to Australia.' },
                    4: { word: 'platypus', description: 'A mammal that lays eggs and has a duck-like bill.' },
                    5: { word: 'rhinoceros', description: 'A large herbivorous mammal with a horn on its nose.' },
                    6: { word: 'alligator', description: 'A large reptile with a broad snout, found in the Americas.' },
                    7: { word: 'crocodile', description: 'A large predatory reptile found in tropical regions.' },
                    8: { word: 'chameleon', description: 'A reptile known for its ability to change color.' },
                    9: { word: 'hedgehog', description: 'A small mammal with spines on its back.' },
                    10: { word: 'salamander', description: 'An amphibian with a lizard-like appearance.' }
                }
            };
            let category = "{{ $category }}";
            let level = parseInt("{{ $level }}");
            let lives = 10;
            let hangmanScore = {{ $player->scores->hangman_score }};
            const playerId = {{ $player->id }};
            let score = 0;

            const wordContainer = document.getElementById('word-container');
            const alphabetContainer = document.getElementById('alphabet-container');
            const livesElement = document.getElementById('lives');
            const scoreElement = document.getElementById('score');
            const popup = document.getElementById('popup');
            const popupContent = document.getElementById('popup-content');
            const nextBtn = document.getElementById('next-btn');
            const descriptionContainer = document.getElementById('description-container');
            const canvas = document.getElementById('game-canvas');
            const ctx = canvas.getContext('2d');


            let revealedLetters = [];
            let currentLetterIndex = 0;
            const player = {
                x: 1,
                y: 1,
                width: 300,
                height: 150,
                image: new Image()
            };

            player.image.src = "https://images.squarespace-cdn.com/content/v1/508da03be4b0d28844ddf21c/1534914532437-34NQK7MID0YWD35MTUOB/Rocco.jpg"; 
            player.image.onload = function () {
                drawPlayer();
            };

            function getLevelData() {
                return levels[category][level];
            }

            function drawPlayer() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(player.image, player.x, player.y, player.width, player.height);
            }
    
            function displayWordAndDescription() {
                const levelData = getLevelData();
                wordContainer.innerHTML = '';

                levelData.word.split('').forEach((char, index) => {
                    const letterElement = document.createElement('span');
                    letterElement.textContent = revealedLetters[index] ? `${char} ` : '_ ';
                    wordContainer.appendChild(letterElement);
                });

                descriptionContainer.textContent = `Description: ${levelData.description}`;
            }

            function displayAlphabet() {
                alphabetContainer.innerHTML = '';
                'abcdefghijklmnopqrstuvwxyz'.split('').forEach(letter => {
                    const letterElement = document.createElement('div');
                    letterElement.textContent = letter;
                    letterElement.classList.add('letter');
                    letterElement.addEventListener('click', function () {
                        if (!this.classList.contains('disabled')) {
                            guessLetter(letter);
                            this.classList.add('disabled'); 
                        }
                    });
                    alphabetContainer.appendChild(letterElement);
                });
            }

            function guessLetter(letter) {
                const levelData = getLevelData();
                const selectedWord = levelData.word;

                let correctGuess = false;

                selectedWord.split('').forEach((char, index) => {
                    if (char === letter && !revealedLetters[index]) {
                        revealedLetters[index] = true; 
                        correctGuess = true; 
                    }
                });

                if (correctGuess) {
                    if (revealedLetters.every(Boolean)) {
                        score = lives * 10;
                        hangmanScore += score;
                        updateScores(playerId, score, category, level);
                        showPopup(` Correct! You earned ${score} points! Click "Next" to proceed.`, true);
                    }
                } else {
                    lives--;
                    player.y += 15;
                    livesElement.textContent = `Lives: ${lives}`;
                    if (lives === 0) {
                        showPopup(`You Lose! The word was "${selectedWord}"`, true);
                    }
                }
                displayWordAndDescription();
                drawPlayer();
                scoreElement.textContent = `Score: ${hangmanScore}`;
            }

            function updateScores(playerId, score, category, level) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('/increment/hangman/score', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        player_id: playerId,
                        hangman_score: score,
                        category: category,
                        level: level,
                    }),
                }).catch(err => console.error('Error updating score:', err));
            }

            function resetGame() {
                const levelData = getLevelData();
                revealedLetters = Array(levelData.word.length).fill(false);
                lives = 10;
                displayWordAndDescription();
                displayAlphabet();
                drawPlayer();
            }

            function showPopup(message, showNext = false) {
                popupContent.textContent = message;
                popup.style.display = 'block';
                nextBtn.style.display = showNext ? 'inline-block' : 'none';
            }

            nextBtn.addEventListener('click', function () {
                popup.style.display = 'none';

                level++;
                if (level > 10) {
                    if (category === 'easy') {
                        category = 'medium';
                    } else if (category === 'medium') {
                        category = 'hard';
                    } else {
                        showPopup('Congratulations! You completed all levels!', false);
                        return;
                    }
                    
                    level = 1;
                }

                const newUrl = `/hangman/${category}/${level}`;
                window.location.href = newUrl;
            });


            resetGame();
        });
    </script>
</body>
</html>
