<?php

acf_add_local_field_group(array(
    'key'      => 'group_site_settings',
    'title'    => 'Site Settings',
    'fields'   => array(
        array(
            'key'   => 'tab_general_settings',
            'label' => 'General Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),
        array(
            'key'           => 'field_maintenance_mode',
            'label'         => 'Enable Maintenance Mode',
            'name'          => 'maintenance_mode',
            'type'          => 'true_false',
            'default_value' => 0,
            'ui'            => 1,
            'wrapper'       => array(
                'width' => '50%',
            ),
        ),
        array(
            'key'           => 'field_disable_payments',
            'label'         => 'Disable Payments',
            'name'          => 'disable_payments',
            'type'          => 'true_false',
            'default_value' => 0,
            'ui'            => 1,
            'wrapper'       => array(
                'width' => '50%',
            ),
        ),
        array(
            'key'     => 'field_post_form_title',
            'label'   => 'Contact Form 7',
            'name'    => 'post_form_shortcode',
            'type'          => 'post_object',
            'post_type'     => array('wpcf7_contact_form'),
            'return_format' => 'id',
            'ui'            => 1,
        ),

        array(
            'key'   => 'tab_social_links',
            'label' => 'Social Links',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),

        array(
            'key'           => 'social_repeater',
            'label'         => 'Social Links List',
            'name'          => 'social_repeater',
            'type'          => 'repeater',
            'button_label'  => 'Add Social Link',
            'layout'        => 'row',
            'instructions'  => 'Add and configure social media links',
            'sub_fields'    => array(

                array(
                    'key'   => 'social_url',
                    'label' => 'Social URL',
                    'name'  => 'url',
                    'type'  => 'url',
                ),

                array(
                    'key'           => 'social_icon',
                    'label'         => 'Social Icon',
                    'name'          => 'icon',
                    'type'          => 'image',
                    'return_format' => 'array',
                ),
            ),
        ),

        array(
            'key'   => 'tab_repair_form_settings',
            'label' => 'Repair Form Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),
        array(
            'key'     => 'field_tab_repair_form_settings_product-note',
            'label'   => 'Product Note',
            'name'    => 'repair_form_settings_product-note',
            'type'    => 'wysiwyg',
            'default_value' => 'Усі замовлення обробляються вручну. Після заповнення форми я зв’яжуся з вами для підтвердження та уточнення деталей.Щоб пришвидшити обробку, залиште Instagram, Telegram username.'
        ),

        array(
            'key' => 'field_repair_form_settings',
            'label' => 'Repair Form Steps',
            'name' => 'repair_form_steps',
            'type' => 'group',
            'layout' => 'block',
            'sub_fields' => array(

                array(
                    'key' => 'field_repair_form_title',
                    'label' => 'Repair Form Title',
                    'name' => 'repair_form_title',
                    'type' => 'text',
                    'default_value' => 'Формуємо замовлення',
                ),

                array(
                    'key'   => 'tab_repair_form_step1',
                    'label' => 'Step 1',
                    'type'  => 'tab',
                ),

                array(
                    'key' => 'field_repair_form_step1_text',
                    'label' => 'Step 1 Text',
                    'name' => 'repair_step1_text',
                    'type' => 'wysiwyg',
                    'media_upload' => 0,
                    'default_value' => '',
                ),

                array(
                    'key' => 'field_repair_form_step1_inst_link',
                    'label' => 'Step 1 Instagram Link',
                    'name' => 'repair_step1_inst_link',
                    'type' => 'link',
                    'return_format' => 'array',
                    'default_value' => array(
                        'title' => 'Написати у Instagram',
                        'url'   => '',
                        'target' => '_blank',
                    ),
                ),

                array(
                    'key' => 'field_repair_form_step1_btn',
                    'label' => 'Step 1 Button Text',
                    'name' => 'repair_step1_button',
                    'type' => 'text',
                    'default_value' => 'Продовжити',
                ),

                /*
                |--------------------------------------------------------------------------
                | STEP 2 TAB
                |--------------------------------------------------------------------------
                */

                array(
                    'key'   => 'tab_repair_form_step2',
                    'label' => 'Step 2',
                    'type'  => 'tab',
                ),

                array(
                    'key' => 'field_repair_form_step2_btn',
                    'label' => 'Submit Button Text',
                    'name' => 'repair_step2_button',
                    'type' => 'text',
                    'default_value' => '← Назад',
                ),

            ),
        ),

//        404
        array(
            'key'   => 'tab_404_page',
            'label' => '404 Page Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),
        array(
            'key'    => 'field_404_wrapper',
            'label'  => '404 Page Settings',
            'name'   => 'page_404',
            'type'   => 'group',
            'layout' => 'block',
            'sub_fields' => array(

                array(
                    'key'     => 'field_404_main_title',
                    'label'   => 'Main Title',
                    'name'    => 'main_title',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),

                array(
                    'key'     => 'field_404_title',
                    'label'   => 'Title',
                    'name'    => 'title',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),

                array(
                    'key'     => 'field_404_description',
                    'label'   => 'Description',
                    'name'    => 'description',
                    'type'    => 'textarea',
                    'wrapper' => array('width' => '100%'),
                ),

                array(
                    'key'     => 'field_404_link_text',
                    'label'   => 'Link Text',
                    'name'    => 'link_text',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),

                array(
                    'key'           => 'field_404_bg_image',
                    'label'         => 'Background Image',
                    'name'          => 'background_image',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array('width' => '50%'),
                ),

                array(
                    'key'           => 'field_404_bg_image_mob',
                    'label'         => 'Background Image Mobile',
                    'name'          => 'background_image_mob',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array('width' => '50%'),
                ),
            ),
        ),

//        THANK YOU PAGE
        array(
            'key'   => 'tab_thank_you_page',
            'label' => 'Thank You Page Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),

        array(
            'key'    => 'field_thank_you_wrapper',
            'label'  => 'Thank You Page Settings',
            'name'   => 'thank_you_page',
            'type'   => 'group',
            'layout' => 'block',
            'sub_fields' => array(
                array(
                    'key'     => 'field_thank_you_main_title',
                    'label'   => 'Main Title',
                    'name'    => 'main_title',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),
                array(
                    'key'     => 'field_thank_you_title',
                    'label'   => 'Title',
                    'name'    => 'title',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),
                array(
                    'key'     => 'field_thank_you_description',
                    'label'   => 'Description',
                    'name'    => 'description',
                    'type'    => 'wysiwyg',
                ),
                array(
                    'key'     => 'field_thank_you_link_text',
                    'label'   => 'Link Text',
                    'name'    => 'link_text',
                    'type'    => 'text',
                    'wrapper' => array('width' => '100%'),
                ),
                array(
                    'key'   => 'field_thank_you_link_url',
                    'label' => 'Link URL',
                    'name'  => 'link_url',
                    'type'  => 'url',
                    'wrapper' => array('width' => '100%'),
                ),
                array(
                    'key'           => 'field_thank_you_bg_image',
                    'label'         => 'Background Image',
                    'name'          => 'background_image',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array('width' => '50%'),
                ),
                array(
                    'key'           => 'field_thank_you_bg_image_mob',
                    'label'         => 'Background Image Mobile',
                    'name'          => 'background_image_mob',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array('width' => '50%'),
                ),
            ),
        ),

        array(
            'key'   => 'tab_loader',
            'label' => 'Loader Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),
            array(
                'key' => 'loader_theme_mode',
                'label' => 'Light Mode Loader',
                'name' => 'loader_light_mode',
                'type' => 'true_false',
                'ui' => 1,
                'ui_on_text' => 'Light',
                'ui_off_text' => 'Dark',
                'default_value' => 0,
                'instructions' => 'Switch loader theme between dark and light',
            ),
            array(
                'key' => 'loader_spinner_toggle',
                'label' => 'Enable Rotating Ring',
                'name' => 'loader_spinner_enabled',
                'type' => 'true_false',
                'ui' => 1,
                'ui_on_text' => 'On',
                'ui_off_text' => 'Off',
                'default_value' => 1,
                'instructions' => 'Enable or disable the rotating loader ring',
            ),

        array(
            'key'   => 'tab_comments',
            'label' => 'Single Post Comments Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),
        array(
            'key'     => 'field_comments_title',
            'label'   => 'Comments Main Title',
            'name'    => 'comments_main_title',
            'type'    => 'text',
            'default_value' => 'Щоденник майстра'
        ),
        array(
            'key'     => 'field_comments_subtitle',
            'label'   => 'Comments Main Subtitle',
            'name'    => 'comments_subtitle_title',
            'type'    => 'text',
            'default_value' => 'Думки, враження та маленькі історії про прикраси'
        ),
        array(
            'key'     => 'field_comments_form_title',
            'label'   => 'Form Title',
            'name'    => 'comments_form_title',
            'type'    => 'text',
            'default_value' => 'Залишити запис у щоденнику'
        ),
        array(
            'key'     => 'field_comments_label_name',
            'label'   => 'Form Label Name',
            'name'    => 'comments_label_name',
            'type'    => 'text',
            'default_value' => 'Ім’я',
        ),
        array(
            'key'     => 'field_comments_label_name_placeholder',
            'label'   => 'Form Label Name Placeholder',
            'name'    => 'comments_label_name_placeholder',
            'type'    => 'text',
            'default_value' => 'Анна Клевлина',
        ),

        array(
            'key'     => 'field_comments_label_text',
            'label'   => 'Form Label Text',
            'name'    => 'comments_label_text',
            'type'    => 'text',
            'default_value' => 'Коментар',
        ),
        array(
            'key'     => 'field_comments_label_text_placeholder',
            'label'   => 'Form Label Text Placeholder',
            'name'    => 'comments_label_text_placeholder',
            'type'    => 'text',
            'default_value' => 'Ваші враження, думки або просто “вау” — мені справді важливо це знати',
        ),
        array(
            'key'           => 'field_comments_empty_text',
            'label'         => 'Text When No Reviews',
            'name'          => 'comments_empty_text',
            'type'          => 'textarea',
            'rows'          => 2,
            'new_lines'     => 'br',
            'default_value' => 'Тут ще немає відгуків. Буду щиро рада, якщо Ви залишите перший.',
        ),
        array(
            'key'     => 'field_comments_button_text',
            'label'   => 'Form Button Text',
            'name'    => 'comments_button_text',
            'type'    => 'text',
            'default_value' => 'Надіслати коментар',
        ),

        array(
            'key'   => 'tab_single_p_settings',
            'label' => 'Single Post Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),
        array(
            'key'     => 'field_tab_single_p_settings_product-note',
            'label'   => 'Product Note',
            'name'    => 'single_p_settings_product-note',
            'type'    => 'wysiwyg',
            'default_value' => 'Усі замовлення обробляються вручну. Після заповнення форми я зв’яжуся з вами для підтвердження та уточнення деталей.Щоб пришвидшити обробку, залиште Instagram, Telegram username.'
        ),

        /*
        |--------------------------------------------------------------------------
        | PRODUCT FAQ (accordion under the description)
        |--------------------------------------------------------------------------
        | Delivery terms come from the order form's Step 1 text below; these two
        | items are the short answers about coating and sizing. Leave a text
        | empty to hide that item.
        */
        array(
            'key'    => 'field_product_faq',
            'label'  => 'Product FAQ',
            'name'   => 'product_faq',
            'type'   => 'group',
            'layout' => 'block',
            'sub_fields' => array(

                array(
                    'key'   => 'field_product_faq_coating_title',
                    'label' => 'Coating — Title',
                    'name'  => 'coating_title',
                    'type'  => 'text',
                    'default_value' => 'Тип покриття: родій або позолота',
                ),

                array(
                    'key'   => 'field_product_faq_coating_text',
                    'label' => 'Coating — Text',
                    'name'  => 'coating_text',
                    'type'  => 'wysiwyg',
                    'media_upload' => 0,
                    'default_value' =>
                        '<p>Срібло з часом природно темніє. Родій — метал платинової групи: тонкий шар створює захисний бар’єр від поту, косметики та побутової хімії, додає дзеркального білого блиску, краще тримається проти дрібних подряпин і не викликає алергії.</p>'
                        . '<p>Позолота працює так само, але дає теплий золотистий відтінок.</p>'
                        . '<p>Покриття обираєте під час оформлення замовлення. Воно впливає на фінальну вартість — я розрахую точну ціну й підтверджу її перед оплатою.</p>',
                ),

                array(
                    'key'   => 'field_product_faq_size_title',
                    'label' => 'Ring Size — Title (rings only)',
                    'name'  => 'size_title',
                    'type'  => 'text',
                    'default_value' => 'Як визначити свій розмір',
                ),

                array(
                    'key'   => 'field_product_faq_size_text',
                    'label' => 'Ring Size — Text (rings only)',
                    'name'  => 'size_text',
                    'type'  => 'wysiwyg',
                    'media_upload' => 0,
                    'default_value' =>
                        '<p>Найточніше — приміряти тоненьку класичну каблучку в ювелірній крамниці або скористатись пальцеміром.</p>'
                        . '<p>Другий варіант — застосунок на телефоні (наприклад, Ring Sizer): прикладаєте рівну, недеформовану каблучку до екрана й підганяєте коло під її внутрішній діаметр.</p>'
                        . '<p>Якщо нічого з цього немає — обгорніть палець ниточкою або смужкою паперу, зробіть позначку на місці стику й повідомте мені довжину. Вимірюйте наприкінці дня, коли палець найбільший, і повторіть заміри 2–3 рази.</p>',
                ),
            ),
        ),

        array(
            'key' => 'field_single_form_settings',
            'label' => 'Form Steps',
            'name' => 'single_form_steps',
            'type' => 'group',
            'layout' => 'block',
            'sub_fields' => array(

                array(
                    'key' => 'field_form_title',
                    'label' => 'Form Title',
                    'name' => 'form_title',
                    'type' => 'text',
                    'default_value' => 'Формуємо замовлення',
                ),

                array(
                    'key'   => 'tab_form_step1',
                    'label' => 'Step 1',
                    'type'  => 'tab',
                ),

                array(
                    'key' => 'field_form_step1_text',
                    'label' => 'Step 1 Text',
                    'name' => 'step1_text',
                    'type' => 'wysiwyg',
                    'media_upload' => 0,
                    'default_value' =>
                        '',
                ),

                array(
                    'key' => 'field_form_step1_btn',
                    'label' => 'Step 1 Button Text',
                    'name' => 'step1_button',
                    'type' => 'text',
                    'default_value' => 'Продовжити',
                ),

                /*
                |--------------------------------------------------------------------------
                | STEP 2 TAB
                |--------------------------------------------------------------------------
                */

                array(
                    'key'   => 'tab_form_step2',
                    'label' => 'Step 2',
                    'type'  => 'tab',
                ),

                array(
                    'key' => 'field_form_step2_btn',
                    'label' => 'Submit Button Text',
                    'name' => 'step2_button',
                    'type' => 'text',
                    'default_value' => '← Назад',
                ),

            ),
        ),

        array(
            'key'   => 'tab_seo_settings',
            'label' => 'SEO Settings',
            'name'  => '',
            'type'  => 'tab',
            'placement' => 'top',
        ),

        array(
            'key'     => 'field_seo_site_title',
            'label'   => 'Site SEO Title',
            'name'    => 'site_seo_title',
            'type'    => 'text',
            'instructions' => 'Default title for homepage and fallback for posts',
            'wrapper' => array(
                'width' => '50%',
            ),
        ),

        array(
            'key'     => 'field_seo_site_description',
            'label'   => 'Site SEO Description',
            'name'    => 'site_seo_description',
            'type'    => 'textarea',
            'rows'    => 3,
            'instructions' => 'Default description for homepage and fallback for posts',
            'wrapper' => array(
                'width' => '50%',
            ),
        ),

        array(
            'key'           => 'field_seo_site_og_image',
            'label'         => 'Default OG Image',
            'name'          => 'site_og_image',
            'type'          => 'image',
            'return_format' => 'url',
            'preview_size'  => 'medium',
            'library'       => 'all',
            'instructions'  => 'Used when post does not have its own image',
        ),

        array(
            'key'          => 'field_google_site_verification',
            'label'        => 'Google Search Console verification',
            'name'         => 'google_site_verification',
            'type'         => 'text',
            'instructions' => 'Код з Google Search Console (метод HTML-тег). Вставте лише значення content="...", без лапок і без всього тега.',
            'placeholder'  => 'abc123xyz...',
        ),

        array(
            'key'   => 'tab_viewed_section',
            'label' => 'Переглянуті товари',
            'type'  => 'tab',
        ),

        array(
            'key'           => 'field_related_section_title',
            'label'         => 'Заголовок секції «Схожі вироби»',
            'name'          => 'related_section_title',
            'type'          => 'text',
            'default_value' => 'Схожі вироби',
            'instructions'  => 'Добірка за типом виробу та каменем на сторінках товару.',
        ),

        array(
            'key'           => 'field_viewed_section_title',
            'label'         => 'Заголовок секції',
            'name'          => 'viewed_section_title',
            'type'          => 'text',
            'default_value' => 'Ви переглядали',
            'instructions'  => 'Заголовок блоку «Переглянуті товари» на сторінках товару.',
        ),

        array(
            'key'           => 'field_viewed_section_tap_text',
            'label'         => 'Підказка на картці (Tap Text)',
            'name'          => 'viewed_section_tap_text',
            'type'          => 'text',
            'default_value' => 'Більше про виріб',
            'instructions'  => 'Напис поверх картки товару (наприклад «Більше про виріб»). Порожньо — не показувати.',
        ),

    ),
    'location' => array(
        array(
            array(
                'param'    => 'options_page',
                'operator' => '==',
                'value'    => 'global_settings',
            ),
        ),
    ),
));
