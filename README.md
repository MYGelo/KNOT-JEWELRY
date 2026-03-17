# Digiway Theme

---

<div align="center">

**🇬🇧 [English](#english) | 🇺🇦 [Українська](#українська)**

</div>

---

<a name="english"></a>

# English

Custom WordPress theme with WooCommerce support. The theme includes a custom Gutenberg
blocks system based on ACF fields, resource loading optimization, and a flexible settings
system via ACF.

## 🚀 Key Features

- ✅ **WooCommerce Integration** — full WooCommerce integration with custom templates
- ✅ **ACF Blocks** — custom Gutenberg blocks system with ACF fields
- ✅ **Performance Optimization** — automatic preloading of critical resources
- ✅ **Maintenance Mode** — maintenance mode functionality
- ✅ **Cart Popup** — shopping cart popup
- ✅ **Auto CSS/JS Enqueue** — automatic styles and scripts enqueuing

## 📦 Requirements

### Required Plugins:

- **Advanced Custom Fields (ACF)** — for working with custom fields and blocks

### Recommended Plugins:

- **WooCommerce** — if using e-commerce functionality

## 📁 Project Structure

```
default-wp-theme/
├── assets/                    # Static resources
│   ├── css/                   # Styles
│   │   ├── components/        # Component styles
│   │   ├── global.css         # Global styles
│   │   └── checkout.css       # Checkout page styles
│   ├── js/                    # JavaScript files
│   │   ├── main.js            # Main script
│   │   ├── woocommerce.js     # WooCommerce scripts
│   │   └── swiper.min.js      # Swiper.js library
│   ├── font/                  # Fonts
│   └── images/                # Images
├── inc/                       # PHP modules
│   ├── acf/                   # ACF integration
│   │   ├── acf.php            # Main ACF configuration
│   │   ├── options-pages.php  # Options pages
│   │   ├── registration.php   # Block registration
│   │   └── options-pages-fields/ # Options page fields
│   ├── enqueue-scripts.php    # Scripts and styles enqueuing
│   ├── maintenance-page.php   # Maintenance mode
│   ├── preloads.php           # Resource preloading
│   ├── theme.php              # Main theme functions
│   ├── utils.php              # Utility functions
│   └── woocommerce.php        # WooCommerce integration
├── template-parts/            # Template parts
│   ├── components/            # Components (header, basket, popup)
│   ├── gutenberg-blocks/      # Custom Gutenberg blocks
│   └── popups/                # Popups
├── templates/                 # Custom page templates
│   ├── template-checkout.php
│   ├── template-login.php
│   ├── template-signup.php
│   └── template-reset-password.php
├── woocommerce/               # WooCommerce templates
│   ├── cart/
│   └── checkout/
├── functions.php              # Main theme file
├── header.php                 # Site header
├── footer.php                 # Site footer
├── index.php                  # Main template
├── home.php                   # Blog template
├── single.php                 # Post template
└── 404.php                    # 404 page
```

## ⚙️ Configuration

### 1. Navigation Menus

To configure menus, go to **Appearance → Menus** and create menus with the following
locations:

- **Main Menu** — main menu in the header
- **Footer Menu** — footer menu

Menu registration code is located in `inc/theme.php`:

```php
register_nav_menus(array(
    'main-menu'   => esc_html__('Main Menu'),
    'menu-footer' => esc_html__('Footer Menu'),
));
```

### 2. ACF Options Pages

The theme automatically creates options pages in the admin panel:

- **Theme Settings** (main page)
    - **Global Settings** — global theme settings
    - **Settings Header** — header settings (logo, etc.)
    - **Settings Footer** — footer settings

Fields for these pages are located in `inc/acf/options-pages-fields/`.

### 3. Popups

The theme supports a popup system. An example popup is located in
`template-parts/popups/example-popup.php`.

**Using a popup:**

```html

<button
	data-action="togglePopup"
	data-target="#example_popup"
>
	Open Popup
</button>
```

> [!NOTE]
> Popups need to be included in `footer.php`.

### 4. Maintenance Mode

Activate maintenance mode through the ACF options page **Global Settings** →
`maintenance_mode` field.

When the mode is active, all users (except administrators) will be redirected to the
`/maintenance` page.

### 5. Disable WooCommerce Payments

If you need to temporarily disable the ability to make purchases, use the option in *
*Global Settings** → `disable_payments` field.

## 🎨 Custom Gutenberg Blocks

The theme supports creating custom Gutenberg blocks with ACF fields.

**Block structure:**

```
gutenberg-blocks/
└── example-block/
    ├── index.php       # Block registration
    ├── fields.php      # ACF fields
    ├── template.php    # Display template
    └── assets/
        ├── style.css   # Block styles
        └── script.js   # JavaScript (optional)
```

**Automatic preloading:** The theme automatically preloads styles and images from the
first block on the page for speed optimization.

## 🛒 WooCommerce

### Custom Templates

The theme overrides the following WooCommerce templates:

- `woocommerce/cart/mini-cart.php` — mini cart
- `woocommerce/checkout/*` — all checkout page templates
- `woocommerce/coupon.php` — coupons

### Custom Pages

- **Checkout** — use the "Checkout" template for the checkout page
- **Login** — "Login" template for WooCommerce customer login
- **Signup** — "Signup" template for WooCommerce customer registration
- **Reset Password** — "Reset Password" template for WooCommerce customer password reset

### AJAX Functionality

- Update cart item quantities via AJAX
- Cart fragments for updating without page reload
- Custom redirects after login/logout/password reset

## 📝 If WooCommerce is Not Used

If you are not using WooCommerce, follow these steps:

1. **Remove WooCommerce scripts enqueuing** in `inc/enqueue-scripts.php`:
   ```php
   // Remove these lines:
   wp_enqueue_script('wc-cart-fragments');
   wp_enqueue_script('wc-add-to-cart');
   wp_enqueue_script('cart-js', ...);
   ```

2. **Remove jQuery enqueuing** (if not used):
   ```php
   // Uncomment in inc/enqueue-scripts.php:
   wp_deregister_script('jquery');
   ```

3. **Remove files:**
    - `inc/woocommerce.php`
    - `assets/js/woocommerce.js`
    - Entire `woocommerce/` folder
    - All files from `templates/` folder

4. **Remove from `functions.php`:**
   ```php
   require_once __DIR__ . '/inc/woocommerce.php';
   ```

5. **Remove cart components from** `header.php`:
   ```php
   <?php get_template_part('template-parts/components/basket'); ?>
   <?php get_template_part('template-parts/components/header/cart-popup'); ?>
   ```

6. **Remove jQuery preload from** `inc/preloads.php`
   ```php
   <link
       rel="preload"
       href="/wp-includes/js/jquery/jquery.min.js?ver=3.7.1"
       as="script"
   >
   <link
       rel="preload"
       href="/wp-includes/js/jquery/jquery-migrate.min.js?ver=3.4.1"
       as="script"
   >
   ```

## 🔧 Automatic Styles Enqueuing

The theme automatically enqueues all CSS files from the `assets/css/` folder, except
excluded ones:

- `header.css`
- `global.css`
- `popup.css`
- `cart-popup.css`

These files are enqueued manually via `inc/preloads.php` for loading optimization.

Styles are enqueued with `media="print"` and `onload="this.media='all'"` attributes for
asynchronous loading.

## 🎯 JavaScript Functionality

The main script `assets/js/main.js` includes:

- **Mobile Menu** — mobile menu toggle
- **Popups** — open/close popups
- **Cart** — open/close cart
- **Video Controls** — video playback control
- **External Links** — automatic addition of `target="_blank"` for external links
- **Menu Arrows** — automatic addition of arrows for menu items with submenus

## 📦 Dependencies

### JavaScript Libraries:

- **Swiper.js** — for sliders (included minified version)

## 🚀 Automatic Deployment

The theme includes automatic deployment via GitHub Actions. When you push changes to the `main` branch or merge a pull request, the deployment workflow automatically:

1. **Minifies CSS and JavaScript** — all CSS and JS files are automatically minified using:
   - **Terser** — for JavaScript minification
   - **PostCSS** with **postcss-nesting** and **cssnano** — for CSS processing and minification

2. **Deploys to SFTP server** — minified files are uploaded to your production server via SFTP

### Deployment Triggers

The deployment workflow runs automatically when:
- You push directly to the `main` branch
- A pull request is merged into the `main` branch

### Configuration

To set up automatic deployment, configure the following GitHub Secrets in your repository settings (**Settings → Secrets and variables → Actions**):

- `SERVER` — SFTP server address
- `USERNAME` — SFTP username
- `PASSWORD` — SFTP password
- `PORT` — SFTP port (usually 22)
- `THEME_PATH` — remote path to the theme directory on the server

### Files Minified

The following directories are processed during deployment:

- `assets/js/` — all JavaScript files
- `assets/css/` — all CSS files
- `template-parts/gutenberg-blocks/` — CSS and JS files in block assets

> [!NOTE]
> The minification process uses PostCSS with nesting support and cssnano for safe CSS minification that preserves functionality. JavaScript is minified using Terser with default settings.

### Workflow File

The deployment configuration is located in `.github/workflows/deploy.yml`. You can customize the deployment process by editing this file.

---

<a name="українська"></a>

# Українська

Користувацька тема WordPress з підтримкою WooCommerce. Тема включає систему кастомних
Gutenberg блоків, оптимізацію завантаження ресурсів та гнучку систему налаштувань через
ACF.

## 🚀 Основні можливості

- ✅ **WooCommerce Integration** — повна інтеграція з WooCommerce з кастомними шаблонами
- ✅ **ACF Blocks** — система кастомних Gutenberg блоків з ACF полями
- ✅ **Performance Optimization** — автоматичне прелоадинг критичних ресурсів
- ✅ **Maintenance Mode** — режим технічного обслуговування
- ✅ **Cart Popup** — спливаюче вікно кошика
- ✅ **Auto CSS/JS Enqueue** — автоматичне підключення стилів та скриптів

## 📦 Вимоги

### Обов'язкові плагіни:

- **Advanced Custom Fields (ACF)** — для роботи з кастомними полями та блоками

### Рекомендовані плагіни:

- **WooCommerce** — якщо використовується e-commerce функціональність

## 📁 Структура проекту

```
default-wp-theme/
├── assets/                    # Статичні ресурси
│   ├── css/                   # Стилі
│   │   ├── components/        # Стилі компонентів
│   │   ├── global.css         # Глобальні стилі
│   │   └── checkout.css       # Стилі checkout сторінки
│   ├── js/                    # JavaScript файли
│   │   ├── main.js            # Основний скрипт
│   │   ├── woocommerce.js     # WooCommerce скрипти
│   │   └── swiper.min.js      # Swiper.js бібліотека
│   ├── font/                  # Шрифти
│   └── images/                # Зображення
├── inc/                       # PHP модулі
│   ├── acf/                   # ACF інтеграція
│   │   ├── acf.php            # Основна конфігурація ACF
│   │   ├── options-pages.php  # Опційні сторінки
│   │   ├── registration.php   # Реєстрація блоків
│   │   └── options-pages-fields/ # Поля опційних сторінок
│   ├── enqueue-scripts.php    # Підключення скриптів та стилів
│   ├── maintenance-page.php   # Режим технічного обслуговування
│   ├── preloads.php           # Прелоадинг ресурсів
│   ├── theme.php              # Основні функції теми
│   ├── utils.php              # Допоміжні функції
│   └── woocommerce.php        # WooCommerce інтеграція
├── template-parts/            # Частини шаблонів
│   ├── components/            # Компоненти (header, basket, popup)
│   ├── gutenberg-blocks/      # Кастомні Gutenberg блоки
│   └── popups/                # Попапи
├── templates/                 # Кастомні шаблони сторінок
│   ├── template-checkout.php
│   ├── template-login.php
│   ├── template-signup.php
│   └── template-reset-password.php
├── woocommerce/               # WooCommerce шаблони
│   ├── cart/
│   └── checkout/
├── functions.php              # Головний файл теми
├── header.php                 # Шапка сайту
├── footer.php                 # Підвал сайту
├── index.php                  # Основний шаблон
├── home.php                   # Шаблон блогу
├── single.php                 # Шаблон поста
└── 404.php                    # Сторінка 404
```

## ⚙️ Налаштування

### 1. Меню навігації

Для налаштування меню перейдіть у **Зовнішній вигляд → Меню** та створіть меню з такими
локаціями:

- **Main Menu** — головне меню в хедері
- **Footer Menu** — меню в футері

Код реєстрації меню знаходиться в `inc/theme.php`:

```php
register_nav_menus(array(
    'main-menu'   => esc_html__('Main Menu'),
    'menu-footer' => esc_html__('Footer Menu'),
));
```

### 2. ACF Опційні сторінки

Тема автоматично створює опційні сторінки в адмін-панелі:

- **Theme Settings** (головна сторінка)
    - **Global Settings** — глобальні налаштування теми
    - **Settings Header** — налаштування хедера (логотип тощо)
    - **Settings Footer** — налаштування футера

Поля для цих сторінок знаходяться в `inc/acf/options-pages-fields/`.

### 3. Попапи

Тема підтримує систему попапів. Приклад попапу знаходиться в
`template-parts/popups/example-popup.php`.

**Використання попапу:**

```html

<button
	data-action="togglePopup"
	data-target="#example_popup"
>
	Відкрити попап
</button>
```

> [!NOTE]
> Попапи потрібно підключати в `footer.php`.

### 4. Режим технічного обслуговування

Активуйте режим технічного обслуговування через ACF опційну сторінку **Global Settings** →
поле `maintenance_mode`.

Коли режим активний, всі користувачі (крім адміністраторів) будуть перенаправлені на
сторінку `/maintenance`.

### 5. Відключення платежів WooCommerce

Якщо потрібно тимчасово відключити можливість покупок, використайте опцію в **Global
Settings** → поле `disable_payments`.

## 🎨 Кастомні Gutenberg блоки

Тема підтримує створення кастомних Gutenberg блоків з ACF полями.

**Структура блоку:**

```
gutenberg-blocks/
└── example-block/
    ├── index.php       # Реєстрація блоку
    ├── fields.php      # ACF поля
    ├── template.php    # Шаблон відображення
    └── assets/
        ├── style.css   # Стилі блоку
        └── script.js   # JavaScript (опційно)
```

**Автоматичний прелоадинг:** Тема автоматично прелоадить стилі та зображення з першого
блоку на сторінці для оптимізації швидкості.

## 🛒 WooCommerce

### Кастомні шаблони

Тема перевизначає наступні WooCommerce шаблони:

- `woocommerce/cart/mini-cart.php` — міні-кошик
- `woocommerce/checkout/*` — всі шаблони checkout сторінки
- `woocommerce/coupon.php` — купони

### Кастомні сторінки

- **Checkout** — використовуйте шаблон "Checkout" для сторінки checkout
- **Login** — шаблон "Login" для сторінки входу клієнта Woocommerce
- **Signup** — шаблон "Signup" для реєстрації клієнта Woocommerce
- **Reset Password** — шаблон "Reset Password" для скидання пароля клієнта Woocommerce

### AJAX функціональність

- Оновлення кількості товарів у кошику через AJAX
- Фрагменти кошика для оновлення без перезавантаження сторінки
- Кастомні редиректи після входу/виходу/скидання пароля

## 📝 Якщо немає WooCommerce

Якщо ви не використовуєте WooCommerce, виконайте наступні кроки:

1. **Видаліть підключення WooCommerce скриптів** в `inc/enqueue-scripts.php`:
   ```php
   // Видаліть ці рядки:
   wp_enqueue_script('wc-cart-fragments');
   wp_enqueue_script('wc-add-to-cart');
   wp_enqueue_script('cart-js', ...);
   ```

2. **Видаліть підключення jQuery** (якщо не використовується):
   ```php
   // Розкоментуйте в inc/enqueue-scripts.php:
   wp_deregister_script('jquery');
   ```

3. **Видаліть файли:**
    - `inc/woocommerce.php`
    - `assets/js/woocommerce.js`
    - Всю папку `woocommerce/`
    - Всі файли з папки `templates/`

4. **Видаліть з `functions.php`:**
   ```php
   require_once __DIR__ . '/inc/woocommerce.php';
   ```

5. **Видаліть компоненти кошика з** `header.php`:
   ```php
   <?php get_template_part('template-parts/components/basket'); ?>
   <?php get_template_part('template-parts/components/header/cart-popup'); ?>
   ```

6. **Видаліть preload для jQuery з** `inc/preloads.php`
   ```php
   <link
       rel="preload"
       href="/wp-includes/js/jquery/jquery.min.js?ver=3.7.1"
       as="script"
   >
   <link
       rel="preload"
       href="/wp-includes/js/jquery/jquery-migrate.min.js?ver=3.4.1"
       as="script"
   >
   ```

## 🔧 Автоматичне підключення стилів

Тема автоматично підключає всі CSS файли з папки `assets/css/`, крім виключених:

- `header.css`
- `global.css`
- `popup.css`
- `cart-popup.css`

Ці файли підключаються вручну через `inc/preloads.php` для оптимізації завантаження.

Стилі підключаються з атрибутом `media="print"` та `onload="this.media='all'"` для
асинхронного завантаження.

## 🎯 JavaScript функціональність

Основний скрипт `assets/js/main.js` включає:

- **Мобільне меню** — перемикач мобільного меню
- **Попапи** — відкриття/закриття попапів
- **Кошик** — відкриття/закриття кошика
- **Відео контроли** — управління відтворенням відео
- **Зовнішні посилання** — автоматичне додавання `target="_blank"` для зовнішніх посилань
- **Стрілки для меню** — автоматичне додавання стрілок для пунктів меню з підменю

## 📦 Залежності

### JavaScript бібліотеки:

- **Swiper.js** — для слайдерів (включена мінімізована версія)

## 🚀 Автоматичний деплой

Тема включає автоматичний деплой через GitHub Actions. Коли ви пушите зміни в гілку `main` або мержите pull request, workflow автоматично:

1. **Мініфікує CSS та JavaScript** — всі CSS та JS файли автоматично мініфікуються за допомогою:
   - **Terser** — для мініфікації JavaScript
   - **PostCSS** з **postcss-nesting** та **cssnano** — для обробки та мініфікації CSS

2. **Деплоїть на SFTP сервер** — мініфіковані файли завантажуються на ваш продакшн сервер через SFTP

### Тригери деплою

Workflow деплою запускається автоматично коли:
- Ви пушите напряму в гілку `main`
- Pull request мержиться в гілку `main`

### Налаштування

Для налаштування автоматичного деплою, налаштуйте наступні GitHub Secrets в налаштуваннях репозиторію (**Settings → Secrets and variables → Actions**):

- `SERVER` — адреса SFTP сервера
- `USERNAME` — ім'я користувача SFTP
- `PASSWORD` — пароль SFTP
- `PORT` — порт SFTP (зазвичай 22)
- `THEME_PATH` — віддалений шлях до директорії теми на сервері

### Мініфіковані файли

Під час деплою обробляються наступні директорії:

- `assets/js/` — всі JavaScript файли
- `assets/css/` — всі CSS файли
- `template-parts/gutenberg-blocks/` — CSS та JS файли в assets блоків

> [!NOTE]
> Процес мініфікації використовує PostCSS з підтримкою nesting та cssnano для безпечної мініфікації CSS, яка зберігає функціональність. JavaScript мініфікується за допомогою Terser з налаштуваннями за замовчуванням.

### Файл Workflow

Конфігурація деплою знаходиться в `.github/workflows/deploy.yml`. Ви можете налаштувати процес деплою, відредагувавши цей файл.
