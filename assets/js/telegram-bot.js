document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('form.wpcf7-form');
    if (!form) return; // Если формы нет, выходим
    // wpcf7submit
    document.addEventListener('wpcf7mailsent', function(event) {

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

        // Формируем сообщение для Telegram
        let message = `
            💎 Новое сообщение с сайта KNŌT JEWELRY:
            
Имя: ${data['first-name']} ${data['last-name']};

Телефон: ${data['your-phone']};
Email: ${data['your-email']};

Сообщение:${data['your-message']};
            
Товар: ${data['product-title']};
Цена: ${data['product-price']};
Размер: ${data['ring-size']};
Материал: ${data['product-material']};
Камень: ${data['product-stone']};
Тип: ${data['product-type']};
            
Ссылка: ${data['product-link']}
`;

        // Отправка через Telegram API
        fetch(`https://api.telegram.org/bot8622055916:AAG9C41IjiLjaIqSskYbfOyi0wTrX_A90Ls/sendMessage`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chat_id: "@KnotJewelryOrders", text: message })
        })
            .then(res => console.log('Telegram OK'))
            .catch(err => console.error('Telegram ERROR', err));

    }, false);

});