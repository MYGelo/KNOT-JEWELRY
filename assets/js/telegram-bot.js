document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('form.wpcf7-form');
    if (!form) return;

    // =========================
    // 🧠 ANTI BOT STATE
    // =========================

    window.formStartTime = Date.now();
    let userInteracted = false;

    const LAST_SEND_KEY = 'last_form_send';

    ['mousemove', 'scroll', 'keydown', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, () => {
            userInteracted = true;
        }, { once: true });
    });

    // =========================
    // 📌 CF7 HOOK
    // =========================

    document.addEventListener('wpcf7submit', function(event) {

        if (event.detail.status === 'validation_failed') {
            console.log('validation_failed');
            return;
        }

        // =========================
        // 📦 DATA COLLECTION
        // =========================

        const formData = event.detail.inputs;
        let data = {};

        formData.forEach(field => {
            data[field.name] = field.value;
        });

        // =========================
        // 🛡️ ANTI SPAM LAYER
        // =========================

        // 1. honeypot
        if (data['website']) {
            console.log('Bot detected (honeypot)');
            return;
        }

        // 2. privacy checkbox
        // if (!data['privacy-policy'] || data['privacy-policy'] !== 'on') {
        //     console.log('Privacy not accepted');
        //     return;
        // }

        // 3. speed check
        const timeSpent = Date.now() - window.formStartTime;
        if (timeSpent < 2500) {
            console.log('Bot too fast');
            return;
        }

        // 4. interaction check
        if (!userInteracted) {
            console.log('No user interaction');
            return;
        }

        // 5. frequency spam protection
        const lastSend = localStorage.getItem(LAST_SEND_KEY);
        if (lastSend && Date.now() - lastSend < 10000) {
            console.log('Too frequent submit');
            return;
        }
        localStorage.setItem(LAST_SEND_KEY, Date.now());

        // 6. math check
        if (data['math-check'] && data['math-check'].trim() !== '') {
            console.log('Math fail');
            return;
        }

        // =========================
        // 📦 PRODUCT DATA
        // =========================

        const productTitle = document.querySelector('.product-title--js')?.textContent || '';
        const qazwsxedcrfv = '8622055916:AAG9C41IjiLjaIqSskYbfOyi0wTrX_A90Ls';
        const productMaterial = document.querySelector('.product-material--js')?.textContent || '';
        const productStone = document.querySelector('.product-stone--js')?.textContent || '';
        const ytrewq = '@KnotJewelryOrders';
        const productType = document.querySelector('.product-type--js')?.textContent || '';
        const productPrice = document.querySelector('.product-price--js .current')?.textContent || '';
        const polkmnjuhbytgfrted = `https://api.telegram.org/bot${qazwsxedcrfv}/sendMessage`;
        const productLink = window.location.href;

        // =========================
        // 🔧 HELPERS
        // =========================

        function setHiddenValue(name, value) {
            const field = form.querySelector(`input[name="${name}"]`);
            if (field) field.value = value;
        }

        function cleanUsername(value) {
            if (!value) return '';
            value = value.trim();

            if (value.startsWith('http')) {
                try {
                    const url = new URL(value);
                    return url.pathname.replace(/\//g, '');
                } catch (e) {
                    return '';
                }
            }

            return value.replace('@', '');
        }

        function formatTelegram(value) {
            const username = cleanUsername(value);
            return username ? '@' + username : '';
        }

        function formatInstagramLink(value) {
            const username = cleanUsername(value);
            return username ? `https://www.instagram.com/${username}` : '-';
        }

        // =========================
        // 📦 UPDATE HIDDEN FIELDS
        // =========================

        setHiddenValue('product-title', productTitle);
        setHiddenValue('product-material', productMaterial);
        setHiddenValue('product-stone', productStone);
        setHiddenValue('product-type', productType);
        setHiddenValue('product-price', productPrice);
        setHiddenValue('product-link', productLink);

        setHiddenValue('form-start', window.formStartTime);

        // =========================
        // 📊 FINAL DATA
        // =========================

        data['product-title'] = productTitle;
        data['product-material'] = productMaterial;
        data['product-stone'] = productStone;
        data['product-type'] = productType;
        data['product-price'] = productPrice;
        data['product-link'] = productLink;

        const telegram = formatTelegram(data['your-telegram']);
        const instagramLink = formatInstagramLink(data['your-instagram']);

        // =========================
        // 📩 MESSAGE
        // =========================

        let message = `
💎 Новое сообщение с сайта KNŌT JEWELRY:

👤 Имя: ${data['full-name']}

📞 Телефон: ${data['your-phone']}
💬 Telegram: ${telegram || '-'}
📷 Instagram: ${instagramLink || '-'}

📝 Сообщение:
${data['your-message']}

💍 Товар: ${data['product-title']}
💰 Цена: ${data['product-price']}
📏 Размер: ${data['ring-size']}
⚙️ Материал: ${data['product-material']}
💎 Камень: ${data['product-stone']}
📦 Тип: ${data['product-type']}

🔗 Ссылка: ${data['product-link']}
`;

        // =========================
        // 🚀 SEND
        // =========================


        fetch(polkmnjuhbytgfrted, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                chat_id: ytrewq,
                text: message
            })
        })
            .then(() => {
                console.log('Telegram OK');
                window.location.href = '/thank-you-page/';
            })
            .catch(err => console.error('Telegram ERROR', err));

    }, false);

});