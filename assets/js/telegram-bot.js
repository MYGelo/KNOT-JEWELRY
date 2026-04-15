document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('form.wpcf7-form');
    if (!form) return; // Если формы нет, выходим
    // wpcf7submit
    document.addEventListener('wpcf7submit', function(event) {
        if (event.detail.status === 'validation_failed') {
            console.log('validation_failed');
            return
        }

        // Сбор данных из формы
        const formData = event.detail.inputs;
        let data = {};
        formData.forEach(field => {
            data[field.name] = field.value;
        });

        // ===== Дополнительно: берем данные с карточки товара =====
        const productTitle = document.querySelector('.product-title--js')?.textContent || '';
        const productMaterial = document.querySelector('.product-material--js')?.textContent || '';
        const productStone = document.querySelector('.product-stone--js')?.textContent || '';
        const productType = document.querySelector('.product-type--js')?.textContent || '';
        const productPrice = document.querySelector('.product-price--js .current')?.textContent || '';
        const productLink = window.location.href;

        // Функция безопасно обновляет hidden-поле
        function setHiddenValue(name, value) {
            const field = form.querySelector(`input[name="${name}"]`);
            if (field) field.value = value;
        }
        function cleanUsername(value) {
            if (!value) return '';

            value = value.trim();

            // если ссылка — достаем username из URL
            if (value.startsWith('http')) {
                try {
                    const url = new URL(value);
                    return url.pathname.replace(/\//g, '');
                } catch (e) {
                    return '';
                }
            }

            // убираем @ если есть
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

        // Обновляем hidden-поля
        setHiddenValue('product-title', productTitle);
        setHiddenValue('product-material', productMaterial);
        setHiddenValue('product-stone', productStone);
        setHiddenValue('product-type', productType);
        setHiddenValue('product-price', productPrice);
        setHiddenValue('product-link', productLink);

        // Обновляем объект data для Telegram
        data['product-title'] = productTitle;
        data['product-material'] = productMaterial;
        data['product-stone'] = productStone;
        data['product-type'] = productType;
        data['product-price'] = productPrice;
        data['product-link'] = productLink;

        const telegram = formatTelegram(data['your-telegram']);
        const instagramLink = formatInstagramLink(data['your-instagram']);

        // Формируем сообщение для Telegram
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

        // Отправка через WordPress AJAX (токен хранится только на сервере)
        if (!window.knotTelegram?.ajax_url || !window.knotTelegram?.nonce) {
            return;
        }

        fetch(window.knotTelegram.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({
                action: 'send_telegram_message',
                nonce: window.knotTelegram.nonce,
                message
            }).toString()
        })
            .then(res => res.json())
            .then(res => {
                if (!res?.success) {
                    throw new Error('Telegram request failed');
                }
                console.log('Telegram OK');
                window.location.href = '/thank-you-page/';
            })
            .catch(err => console.error('Telegram ERROR', err));

    }, false);

});