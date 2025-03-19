// Funzioni di utilità per la gestione della Card 1
function disableCard1() {
    const card1Elements = document.querySelectorAll('.card-1 button, .card-1 input');
    card1Elements.forEach(el => el.disabled = true);
}

function enableCard1() {
    const card1Elements = document.querySelectorAll('.card-1 button, .card-1 input');
    card1Elements.forEach(el => el.disabled = false);
}

function loadExistingEtichette() {
    const formContainer = document.getElementById('etichetta-form-container');
    if (formContainer) {
        formContainer.innerHTML = '';
    }
}

// Gestione del form
function setupFormEventListeners() {
    const form = document.getElementById('etichetta-form');
    const generateLabelBtn = document.getElementById('generate-label-btn');
    
    // Gestione Annulla
    document.getElementById('cancel-form').addEventListener('click', () => {
        enableCard1();
        loadExistingEtichette();
    });

    // Preview immagine
    const imageInput = document.querySelector('input[name="immagine"]');
    const imagePreview = document.getElementById('image-preview');
    
    imageInput?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.querySelector('img').src = e.target.result;
                imagePreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // Gestione Submit del form
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        console.log('Form submitted'); // Debug

        // Aggiorna il campo nascosto con il contenuto dell'editor
        const ingredientiContent = tinyMCE.get('ingredienti-editor').getContent();
        document.getElementById('ingredienti_content').value = ingredientiContent;
        
        const formData = new FormData(form);
        
        // Debug
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_etichetta',
                nonce: etichetteDnaParams.nonce,
                ...Object.fromEntries(formData)
            },
            success: function(response) {
                console.log('Response:', response); // Debug
                if (response.success) {
                    alert('Etichetta salvata con successo!');
                    enableCard1();
                    loadExistingEtichette();
                    
                    if (formData.get('nome_prodotto') && formData.get('anno') && formData.get('lotto')) {
                        generateLabelBtn.disabled = false;
                    }
                } else {
                    alert('Errore durante il salvataggio: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', {xhr, status, error}); // Debug
                alert('Errore durante il salvataggio');
            }
        });
    });
}

// Funzione per creare il form
function showEtichettaForm(etichettaId) {
    const formHtml = `
        <form id="etichetta-form" class="space-y-4">
            <input type="hidden" name="etichetta_id" value="${etichettaId}">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome prodotto</label>
                    <input type="text" name="nome_prodotto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Anno</label>
                    <input type="number" name="anno" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descrizione prodotto</label>
                    <input type="text" name="descrizione_prodotto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Denominazione prodotto</label>
                    <input type="text" name="denominazione_prodotto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lotto</label>
                    <input type="text" name="lotto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ingredienti</label>
                    <div class="mt-1">
                        <div id="ingredienti-editor"></div>
                        <input type="hidden" name="ingredienti" id="ingredienti_content">
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Immagine</label>
                    <div class="mt-1 flex items-center">
                        <input type="hidden" name="immagine" id="immagine_url">
                        <button type="button" id="upload_image_button" class="rounded-md bg-fuchsia-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-fuchsia-500">
                            Seleziona immagine
                        </button>
                        <div id="image-preview" class="ml-4 h-16 w-16 hidden">
                            <img src="" alt="Preview" class="h-full w-full object-cover rounded-md">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Energia (kJ)</label>
                    <input type="number" step="0.01" name="energia_kj" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Energia (kcal)</label>
                    <input type="number" step="0.01" name="energia_kcal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Grassi</label>
                    <input type="number" step="0.01" name="grassi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">di cui acidi grassi saturi</label>
                    <input type="number" step="0.01" name="acidi_grassi_saturi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Carboidrati</label>
                    <input type="number" step="0.01" name="carboidrati" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">di cui zuccheri</label>
                    <input type="number" step="0.01" name="zuccheri" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Proteine</label>
                    <input type="number" step="0.01" name="proteine" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sale</label>
                    <input type="number" step="0.01" name="sale" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bottiglia</label>
                    <select name="bottiglia_materiale" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                        <option value="">Nessuno</option>
                        <option value="Vetro incolore - GL 70">Vetro incolore - GL 70</option>
                        <option value="Vetro verde - GL 71">Vetro verde - GL 71</option>
                        <option value="Vetro marrone - GL 72">Vetro marrone - GL 72</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Capsula</label>
                    <select name="capsula_materiale" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                        <option value="">Nessuno</option>
                        <option value="Alluminio - ALU 41">Alluminio - ALU 41</option>
                        <option value="Polilaminato-Alluminio - C/ALU 90">Polilaminato-Alluminio - C/ALU 90</option>
                        <option value="Poliaccoppiato - C/PVC 90">Poliaccoppiato - C/PVC 90</option>
                        <option value="Polietilene Tereftalato (PET) - PET 1">Polietilene Tereftalato (PET) - PET 1</option>
                        <option value="Polietilene ad alta densità (PE-HD) - PE-HD 2">Polietilene ad alta densità (PE-HD) - PE-HD 2</option>
                        <option value="Polipropilene (PP) - PP 5">Polipropilene (PP) - PP 5</option>
                        <option value="Polistirene (PS) - PS 6">Polistirene (PS) - PS 6</option>
                        <option value="Polivinilcloruro (PVC) - PVC 3">Polivinilcloruro (PVC) - PVC 3</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Gabbietta</label>
                    <select name="gabbietta_materiale" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                        <option value="">Nessuno</option>
                        <option value="Acciaio - FE 40">Acciaio - FE 40</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tappo</label>
                    <select name="tappo_materiale" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fuchsia-500 focus:ring-fuchsia-500">
                        <option value="">Nessuno</option>
                        <option value="Sughero - FOR 51">Sughero - FOR 51</option>
                        <option value="Polietilene Tereftalato (PET) - PET 1">Polietilene Tereftalato (PET) - PET 1</option>
                        <option value="Polietilene ad alta densità (PE-HD) - PE-HD 2">Polietilene ad alta densità (PE-HD) - PE-HD 2</option>
                        <option value="Polipropilene (PP) - PP 5">Polipropilene (PP) - PP 5</option>
                        <option value="Polistirene (PS) - PS 6">Polistirene (PS) - PS 6</option>
                        <option value="Alluminio - ALU 41">Alluminio - ALU 41</option>
                        <option value="Acciaio - FE 40">Acciaio - FE 40</option>
                    </select>
                </div>
            </div>

            <!-- Aggiungi i pulsanti alla fine del form -->
            <div class="flex justify-end space-x-4 mt-4">
                <button type="submit" class="px-4 py-2 bg-fuchsia-600 text-white rounded-md hover:bg-fuchsia-500">
                    Salva
                </button>
                <button type="button" id="generate-label-btn" class="px-4 py-2 bg-fuchsia-600 text-white rounded-md hover:bg-fuchsia-500 opacity-50 cursor-not-allowed" disabled>
                    Genera Etichetta
                </button>
                <button type="button" id="generate-qr-btn" class="px-4 py-2 bg-fuchsia-600 text-white rounded-md hover:bg-fuchsia-500 opacity-50 cursor-not-allowed" disabled>
                    Genera QrCode
                </button>
                <button type="button" id="cancel-form" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    Annulla
                </button>
            </div>
        </form>
    `;

    const formContainer = document.getElementById('etichetta-form-container');
    if (formContainer) {
        formContainer.innerHTML = formHtml;
        
        // Inizializza Media Library
        const uploadButton = document.getElementById('upload_image_button');
        const imageInput = document.getElementById('immagine_url');
        const imagePreview = document.getElementById('image-preview');
        
        uploadButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const mediaUploader = wp.media({
                title: 'Seleziona immagine',
                button: {
                    text: 'Usa questa immagine'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                imageInput.value = attachment.url;
                imagePreview.querySelector('img').src = attachment.url;
                imagePreview.classList.remove('hidden');
            });

            mediaUploader.open();
        });

        // Inizializza TinyMCE con configurazione minima
        if (typeof wp !== 'undefined' && wp.editor && document.getElementById('ingredienti-editor')) {
            try {
                wp.editor.remove('ingredienti-editor'); // Rimuove eventuali istanze precedenti
                
                wp.editor.initialize('ingredienti-editor', {
                    tinymce: {
                        height: 200,
                        menubar: false,
                        plugins: ['lists', 'paste'],
                        toolbar: 'bold italic | bullist numlist',
                        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; font-size: 14px }',
                        branding: false,
                        elementpath: false,
                        statusbar: false,
                        relative_urls: false,
                        remove_script_host: false,
                        convert_urls: false,
                        setup: function(editor) {
                            editor.on('init', function() {
                                console.log('Editor inizializzato con successo');
                            });
                        }
                    },
                    quicktags: {
                        buttons: 'strong,em,ul,ol,li'
                    },
                    mediaButtons: false
                });
            } catch (error) {
                console.error('Errore durante l\'inizializzazione dell\'editor:', error);
            }
        } else {
            console.error('WordPress editor non disponibile o elemento ingredienti-editor non trovato');
        }

        setupFormEventListeners();
    } else {
        console.error('Container del form non trovato');
    }

    const generateLabelBtn = document.getElementById('generate-label-btn');
    const form = document.getElementById('etichetta-form');
    
    function checkRequiredFields() {
        const nomeProdotto = form.querySelector('[name="nome_prodotto"]').value.trim();
        const anno = form.querySelector('[name="anno"]').value.trim();
        
        console.log('Checking fields:', { nomeProdotto, anno }); // Debug
        
        return nomeProdotto !== '' && anno !== '';
    }

    function updateGenerateButton() {
        const isValid = checkRequiredFields();
        console.log('Fields valid:', isValid); // Debug
        
        if (generateLabelBtn) {
            generateLabelBtn.disabled = !isValid;
            generateLabelBtn.classList.toggle('opacity-50', !isValid);
            generateLabelBtn.classList.toggle('cursor-not-allowed', !isValid);
        }
    }

    // Aggiungi listener per i campi
    const nomeProdottoInput = form.querySelector('[name="nome_prodotto"]');
    const annoInput = form.querySelector('[name="anno"]');
    const lottoInput = form.querySelector('[name="lotto"]');

    nomeProdottoInput?.addEventListener('input', updateGenerateButton);
    annoInput?.addEventListener('input', updateGenerateButton);
    lottoInput?.addEventListener('input', updateGenerateButton);

    // Quando i dati vengono caricati nel form
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'get_etichetta_data',
            nonce: etichetteDnaParams.nonce,
            etichetta_id: etichettaId
        },
        success: function(response) {
            if (response.success) {
                // Popola i campi del form
                const data = response.data;
                
                if (nomeProdottoInput) nomeProdottoInput.value = data.nome_prodotto || '';
                if (annoInput) annoInput.value = data.anno || '';
                if (lottoInput) lottoInput.value = data.lotto || '';
                
                // Verifica lo stato del pulsante dopo aver popolato i campi
                updateGenerateButton();
                
                // Popola i valori nutrizionali
                form.querySelector('[name="energia_kj"]').value = data.energia_kj || '';
                form.querySelector('[name="energia_kcal"]').value = data.energia_kcal || '';
                form.querySelector('[name="grassi"]').value = data.grassi || '';
                form.querySelector('[name="acidi_grassi_saturi"]').value = data.acidi_grassi_saturi || '';
                form.querySelector('[name="carboidrati"]').value = data.carboidrati || '';
                form.querySelector('[name="zuccheri"]').value = data.zuccheri || '';
                form.querySelector('[name="proteine"]').value = data.proteine || '';
                form.querySelector('[name="sale"]').value = data.sale || '';
                
                // Popola i select dei materiali
                form.querySelector('[name="bottiglia_materiale"]').value = data.bottiglia_materiale || '';
                form.querySelector('[name="capsula_materiale"]').value = data.capsula_materiale || '';
                form.querySelector('[name="gabbietta_materiale"]').value = data.gabbietta_materiale || '';
                form.querySelector('[name="tappo_materiale"]').value = data.tappo_materiale || '';
                
                // Gestione immagine
                if (data.immagine) {
                    document.getElementById('immagine_url').value = data.immagine;
                    const imagePreview = document.getElementById('image-preview');
                    imagePreview.querySelector('img').src = data.immagine;
                    imagePreview.classList.remove('hidden');
                }
                
                // Gestione ingredienti con TinyMCE
                if (data.ingredienti) {
                    const editor = tinyMCE.get('ingredienti-editor');
                    if (editor) {
                        editor.setContent(data.ingredienti);
                    }
                    document.getElementById('ingredienti_content').value = data.ingredienti;
                }

                // Aggiorna lo stato del pulsante QR
                updateQrButton(data);
            }
        }
    });

    // Aggiungi l'event listener per il click sul pulsante "Genera Etichetta"
    generateLabelBtn?.addEventListener('click', function() {
        if (!checkRequiredFields()) return;

        // Mostra un indicatore di caricamento
        generateLabelBtn.disabled = true;
        generateLabelBtn.textContent = 'Generazione in corso...';

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_etichetta_html',
                nonce: etichetteDnaParams.nonce,
                etichetta_id: etichettaId,
                nome_prodotto: nomeProdottoInput.value.trim(),
                anno: annoInput.value.trim()
            },
            success: function(response) {
                if (response.success) {
                    alert('Etichetta generata con successo!');
                    // Aggiorna il campo etichetta nel database
                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'update_etichetta_path',
                            nonce: etichetteDnaParams.nonce,
                            etichetta_id: etichettaId,
                            etichetta_path: response.data.file_path
                        }
                    });
                } else {
                    alert('Errore durante la generazione dell\'etichetta: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert('Errore durante la generazione dell\'etichetta');
                console.error('Error:', error);
            },
            complete: function() {
                // Ripristina il pulsante
                generateLabelBtn.disabled = false;
                generateLabelBtn.textContent = 'Genera Etichetta';
                updateGenerateButton();
            }
        });
    });

    const generateQrBtn = document.getElementById('generate-qr-btn');
    
    function updateQrButton(data) {
        if (generateQrBtn) {
            // Il pulsante è cliccabile solo se etichetta è valorizzato e qr_code non lo è
            const isEnabled = data.etichetta && !data.qr_code;
            
            generateQrBtn.disabled = !isEnabled;
            generateQrBtn.classList.toggle('opacity-50', !isEnabled);
            generateQrBtn.classList.toggle('cursor-not-allowed', !isEnabled);
            
            // Debug
            console.log('QR Button state:', {
                etichetta: data.etichetta,
                qr_code: data.qr_code,
                isEnabled: isEnabled
            });
        }
    }

    generateQrBtn?.addEventListener('click', function() {
        generateQrBtn.disabled = true;
        generateQrBtn.textContent = 'Generazione QR Code in corso...';

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_qrcode',
                nonce: etichetteDnaParams.nonce,
                etichetta_id: etichettaId
            },
            success: function(response) {
                if (response.success) {
                    alert('QR Code generato con successo!');
                    // Aggiorna lo stato del pulsante
                    generateQrBtn.disabled = true;
                    generateQrBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    alert('Errore durante la generazione del QR Code: ' + response.data);
                    generateQrBtn.disabled = false;
                }
            },
            error: function(xhr, status, error) {
                alert('Errore durante la generazione del QR Code');
                console.error('Error:', error);
                generateQrBtn.disabled = false;
            },
            complete: function() {
                generateQrBtn.textContent = 'Genera QrCode';
            }
        });
    });
}

// Funzione per creare il box dell'etichetta
function createEtichettaBox(etichetta, index) {
    // Debug log per verificare i dati in arrivo
    console.log('Dati etichetta ricevuti:', etichetta);

    const box = document.createElement('div');
    box.className = index % 2 === 0 ? 
        'p-4 bg-gray-100 rounded-lg mb-2 flex justify-between items-start' : 
        'p-4 bg-white rounded-lg mb-2 border border-gray-200 flex justify-between items-start';
    
    // Contenuto principale
    const contentDiv = document.createElement('div');
    contentDiv.innerHTML = `
        <h3 class="font-medium text-gray-900 mb-1">${etichetta.nome_prodotto || 'Nuovo prodotto'}</h3>
        <p class="text-sm font-bold text-gray-600 mb-1">Anno: ${etichetta.anno || 'N/D'}</p>
        <p class="text-xs text-gray-500">${etichetta.data_creazione || 'Data non impostata'}</p>
    `;

    // Verifica la presenza dei campi etichetta e qr_code
    const hasEtichetta = etichetta.etichetta && etichetta.etichetta !== 'null' && etichetta.etichetta !== '';
    const hasQrCode = etichetta.qr_code && etichetta.qr_code !== 'null' && etichetta.qr_code !== '';

    // Debug log per verificare lo stato dei controlli
    console.log('Stato controlli:', {
        hasEtichetta,
        hasQrCode,
        etichettaValue: etichetta.etichetta,
        qrCodeValue: etichetta.qr_code
    });

    // Pulsanti azione
    const actionDiv = document.createElement('div');
    if (!etichetta.nome_prodotto) {
        // Stato iniziale: solo pulsante "Crea nuova etichetta"
        actionDiv.innerHTML = `
            <button class="create-label-btn px-3 py-2 bg-fuchsia-600 text-white rounded-md text-sm hover:bg-fuchsia-500">
                Crea nuova etichetta
            </button>
        `;
    } else {
        // Stato dopo il salvataggio: pulsanti Modifica, Etichetta, QrCode, Elimina
        actionDiv.innerHTML = `
            <div class="space-x-2">
                <button class="edit-label-btn px-3 py-2 bg-fuchsia-600 text-white rounded-md text-sm hover:bg-fuchsia-500">
                    Modifica
                </button>
                <button class="view-label-btn px-3 py-2 bg-fuchsia-600 text-white rounded-md text-sm hover:bg-fuchsia-500 ${!hasEtichetta ? 'opacity-50 cursor-not-allowed' : ''}" 
                        ${!hasEtichetta ? 'disabled' : ''}>
                    Etichetta
                </button>
                <button class="download-qr-btn px-3 py-2 bg-fuchsia-600 text-white rounded-md text-sm hover:bg-fuchsia-500 ${!hasQrCode ? 'opacity-50 cursor-not-allowed' : ''}"
                        ${!hasQrCode ? 'disabled' : ''}>
                    QrCode
                </button>
                <button class="delete-label-btn px-3 py-2 bg-fuchsia-600 text-white rounded-md text-sm hover:bg-fuchsia-500 ${!hasQrCode ? 'opacity-50 cursor-not-allowed' : ''}"
                        ${!hasQrCode ? 'disabled' : ''}>
                    Elimina
                </button>
            </div>
        `;
    }

    box.appendChild(contentDiv);
    box.appendChild(actionDiv);

    // Event listener per "Crea nuova etichetta"
    const createBtn = actionDiv.querySelector('.create-label-btn');
    if (createBtn) {
        createBtn.addEventListener('click', () => {
            disableCard1();
            showEtichettaForm(etichetta.id);
        });
    }

    // Event listeners per i pulsanti
    const editBtn = actionDiv.querySelector('.edit-label-btn');
    if (editBtn) {
        editBtn.addEventListener('click', () => {
            disableCard1();
            loadEtichettaData(etichetta.id);
        });
    }

    const viewLabelBtn = actionDiv.querySelector('.view-label-btn');
    if (viewLabelBtn && hasEtichetta) {
        // Debug log per il pulsante etichetta
        console.log('Configurazione pulsante etichetta:', {
            hasButton: !!viewLabelBtn,
            isEnabled: hasEtichetta,
            url: etichetta.etichetta
        });
        
        viewLabelBtn.addEventListener('click', () => {
            window.open(etichetta.etichetta, '_blank');
        });
    }

    const downloadQrBtn = actionDiv.querySelector('.download-qr-btn');
    if (downloadQrBtn && hasQrCode) {
        // Debug log per il pulsante QR
        console.log('Configurazione pulsante QR:', {
            hasButton: !!downloadQrBtn,
            isEnabled: hasQrCode,
            url: etichetta.qr_code
        });
        
        downloadQrBtn.addEventListener('click', () => {
            const link = document.createElement('a');
            link.href = etichetta.qr_code;
            link.download = `${(etichetta.nome_prodotto || 'prodotto').replace(/\s+/g, '')}${etichetta.anno || ''}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    // Aggiungiamo l'event listener per il pulsante Elimina
    const deleteBtn = actionDiv.querySelector('.delete-label-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => {
            const confirmMessage = `ATTENZIONE!\n\nStai per eliminare definitivamente questa etichetta.\nQuesta azione:\n` +
                `- Cancellerà tutti i dati dell'etichetta\n` +
                `- Rimuoverà il file HTML generato\n` +
                `- Rimuoverà il QR Code generato\n\n` +
                `Questa azione non può essere annullata.\n\n` +
                `Sei sicuro di voler procedere?`;

            if (window.confirm(confirmMessage)) {
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'delete_etichetta',
                        nonce: etichetteDnaParams.nonce,
                        etichetta_id: etichetta.id
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Etichetta eliminata con successo!');
                            loadExistingEtichette();
                        } else {
                            console.error('Errore risposta server:', response);
                            alert('Errore durante l\'eliminazione: ' + (response.data || 'Errore sconosciuto'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Dettagli errore:', {
                            status: status,
                            error: error,
                            response: xhr.responseText
                        });
                        alert('Errore durante l\'eliminazione dell\'etichetta: ' + error);
                    }
                });
            }
        });
    }

    return box;
}

// Funzione per caricare i dati dell'etichetta
function loadEtichettaData(etichettaId) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'get_etichetta_data',
            nonce: etichetteDnaParams.nonce,
            etichetta_id: etichettaId
        },
        success: function(response) {
            if (response.success) {
                showEtichettaForm(etichettaId);
                
                // Attendiamo che il form sia completamente caricato
                setTimeout(() => {
                    const form = document.getElementById('etichetta-form');
                    const data = response.data;
                    
                    // Popola i campi di testo e numerici
                    form.querySelector('[name="nome_prodotto"]').value = data.nome_prodotto || '';
                    form.querySelector('[name="anno"]').value = data.anno || '';
                    form.querySelector('[name="descrizione_prodotto"]').value = data.descrizione_prodotto || '';
                    form.querySelector('[name="denominazione_prodotto"]').value = data.denominazione_prodotto || '';
                    form.querySelector('[name="lotto"]').value = data.lotto || '';
                    
                    // Popola i valori nutrizionali
                    form.querySelector('[name="energia_kj"]').value = data.energia_kj || '';
                    form.querySelector('[name="energia_kcal"]').value = data.energia_kcal || '';
                    form.querySelector('[name="grassi"]').value = data.grassi || '';
                    form.querySelector('[name="acidi_grassi_saturi"]').value = data.acidi_grassi_saturi || '';
                    form.querySelector('[name="carboidrati"]').value = data.carboidrati || '';
                    form.querySelector('[name="zuccheri"]').value = data.zuccheri || '';
                    form.querySelector('[name="proteine"]').value = data.proteine || '';
                    form.querySelector('[name="sale"]').value = data.sale || '';
                    
                    // Popola i select dei materiali
                    form.querySelector('[name="bottiglia_materiale"]').value = data.bottiglia_materiale || '';
                    form.querySelector('[name="capsula_materiale"]').value = data.capsula_materiale || '';
                    form.querySelector('[name="gabbietta_materiale"]').value = data.gabbietta_materiale || '';
                    form.querySelector('[name="tappo_materiale"]').value = data.tappo_materiale || '';
                    
                    // Gestione immagine
                    if (data.immagine) {
                        document.getElementById('immagine_url').value = data.immagine;
                        const imagePreview = document.getElementById('image-preview');
                        imagePreview.querySelector('img').src = data.immagine;
                        imagePreview.classList.remove('hidden');
                    }
                    
                    // Gestione ingredienti con TinyMCE
                    if (data.ingredienti) {
                        const editor = tinyMCE.get('ingredienti-editor');
                        if (editor) {
                            editor.setContent(data.ingredienti);
                        }
                        document.getElementById('ingredienti_content').value = data.ingredienti;
                    }
                    
                }, 500); // Attende che TinyMCE sia inizializzato
            } else {
                alert('Errore nel caricamento dei dati dell\'etichetta');
            }
        },
        error: function() {
            alert('Errore nella richiesta dei dati dell\'etichetta');
        }
    });
}

// Gestione errori globale
window.onerror = function(msg, url, lineNo, columnNo, error) {
    console.error('Errore globale:', {
        message: msg,
        url: url,
        line: lineNo,
        column: columnNo,
        error: error
    });
    return false;
};

// Inizializzazione quando il DOM è caricato
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script caricato'); // Verifica caricamento script
    
    const addSlotsButton = document.getElementById('add-slots-button');
    const slotsInput = document.getElementById('slots-number');
    const etichetteSummary = document.getElementById('etichette-summary');

    // Funzione per caricare le etichette esistenti
    function loadExistingEtichette() {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_etichette',
                nonce: etichetteDnaParams.nonce
            },
            success: function(response) {
                if (response.success) {
                    etichetteSummary.innerHTML = ''; // Pulisce il contenitore
                    response.data.forEach((etichetta, index) => {
                        const box = createEtichettaBox(etichetta, index);
                        etichetteSummary.appendChild(box);
                    });
                }
            }
        });
    }

    // Funzione per caricare le etichette generate
    function loadGeneratedEtichette() {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_generated_etichette',
                nonce: etichetteDnaParams.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Debug log per vedere la risposta completa
                    console.log('Risposta AJAX:', response.data);
                    
                    const container = document.getElementById('etichette-generated-summary');
                    container.innerHTML = ''; // Pulisce il contenitore
                    
                    response.data.forEach((etichetta, index) => {
                        // Debug log per ogni etichetta
                        console.log('Etichetta singola:', etichetta);
                        
                        const box = createEtichettaBoxDashboard(etichetta, index);
                        container.appendChild(box);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Errore nel caricamento delle etichette:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
            }
        });
    }

    // Carica le etichette esistenti all'avvio
    loadExistingEtichette();

    // Carica le etichette generate all'avvio
    loadGeneratedEtichette();

    // Gestione aggiunta slots
    if (!addSlotsButton || !slotsInput) {
        console.error('Elementi non trovati:', {
            button: !!addSlotsButton,
            input: !!slotsInput
        });
        return;
    }

    console.log('Elementi trovati, aggiungo event listener'); // Verifica elementi trovati

    addSlotsButton.addEventListener('click', function() {
        console.log('Click sul pulsante'); // Verifica click
        
        const numeroSlots = parseInt(slotsInput.value);
        console.log('Numero slots:', numeroSlots); // Verifica valore input
        
        if (isNaN(numeroSlots) || numeroSlots <= 0) {
            alert('Inserisci un numero valido di slots');
            return;
        }

        console.log('Invio richiesta AJAX'); // Verifica prima di AJAX

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'add_slots',
                numero_slots: numeroSlots,
                nonce: etichetteDnaParams.nonce
            },
            success: function(response) {
                console.log('Risposta AJAX ricevuta:', response);
                
                if (response.success) {
                    const stats = response.data.stats;
                    console.log('Aggiorno statistiche:', stats);
                    
                    document.querySelector('.total-slots dd').textContent = stats.total_slots;
                    document.querySelector('.used-slots dd').textContent = stats.used_slots;
                    document.querySelector('.remaining-slots dd').textContent = stats.remaining_slots;
                    
                    slotsInput.value = '';
                    console.log('Statistiche aggiornate');
                } else {
                    console.error('Errore nella risposta:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Errore AJAX:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
            }
        });
    });
});

function createEtichettaBoxDashboard(etichetta, index) {
    // Debug log per verificare i campi
    console.log('Etichetta completa:', etichetta);

    const box = document.createElement('div');
    box.className = index % 2 === 0 ? 
        'p-4 bg-gray-100 rounded-lg mb-2 flex justify-between items-start' : 
        'p-4 bg-white rounded-lg mb-2 border border-gray-200 flex justify-between items-start';
    
    // Contenuto principale
    const contentDiv = document.createElement('div');
    contentDiv.innerHTML = `
        <h5 class="font-medium text-gray-900 mb-1">${etichetta.nome_prodotto || 'Prodotto'}</h5>
        <p class="text-sm font-bold text-gray-600 mb-1">Anno: ${etichetta.anno || 'N/D'}</p>
        <p class="text-xs text-gray-500 mb-0">${etichetta.data_creazione || 'Data non impostata'}</p>
    `;

    // Verifica la presenza dei campi etichetta e qr_code
    const hasEtichetta = etichetta.etichetta && etichetta.etichetta !== 'null' && etichetta.etichetta !== '';
    const hasQrCode = etichetta.qr_code && etichetta.qr_code !== 'null' && etichetta.qr_code !== '';

    // Debug log per verificare lo stato dei pulsanti
    console.log('Stato pulsanti:', {
        hasEtichetta,
        hasQrCode,
        etichettaValue: etichetta.etichetta,
        qrCodeValue: etichetta.qr_code
    });

    // Pulsanti azione
    const actionDiv = document.createElement('div');
    actionDiv.innerHTML = `
        <div class="space-x-2">
            <button class="view-label-btn px-3 py-2 bg-fuchsia-600 text-white rounded-md text-sm hover:bg-fuchsia-500 ${!hasEtichetta ? 'opacity-50 cursor-not-allowed' : ''}" 
                    ${!hasEtichetta ? 'disabled' : ''}>
                Etichetta
            </button>
            <button class="download-qr-btn px-3 py-2 bg-fuchsia-600 text-white rounded-md text-sm hover:bg-fuchsia-500 ${!hasQrCode ? 'opacity-50 cursor-not-allowed' : ''}"
                    ${!hasQrCode ? 'disabled' : ''}>
                QrCode
            </button>
        </div>
    `;

    box.appendChild(contentDiv);
    box.appendChild(actionDiv);

    // Event listeners per i pulsanti
    const viewLabelBtn = actionDiv.querySelector('.view-label-btn');
    if (viewLabelBtn && hasEtichetta) {
        viewLabelBtn.addEventListener('click', () => {
            window.open(etichetta.etichetta, '_blank');
        });
    }

    const downloadQrBtn = actionDiv.querySelector('.download-qr-btn');
    if (downloadQrBtn && hasQrCode) {
        downloadQrBtn.addEventListener('click', () => {
            const link = document.createElement('a');
            link.href = etichetta.qr_code;
            link.download = `${(etichetta.nome_prodotto || 'prodotto').replace(/\s+/g, '')}${etichetta.anno || ''}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    return box;
} 