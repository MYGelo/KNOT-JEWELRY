<?php
acf_add_local_field_group(array(
    'key'      => 'author-block',
    'title'    => 'Author Block',
    'fields'   => array(

        array(
            'key' => 'field_author_photo',
            'label' => 'Photo',
            'name' => 'photo',
            'type' => 'image',
            'return_format' => 'array',
            'preview_size' => 'medium',
        ),

        array(
            'key' => 'field_author_title',
            'label' => 'Name',
            'name' => 'title',
            'type' => 'text',
            'default_value' => 'Aня Клевлина',
        ),

        array(
            'key' => 'field_author_position',
            'label' => 'Position',
            'name' => 'position',
            'type' => 'text',
            'default_value' => 'Ручні срібні прикраси з натуральним камінням',
        ),

        array(
            'key' => 'field_author_short_bio',
            'label' => 'Short Bio',
            'name' => 'short_bio',
            'type' => 'wysiwyg',
            'default_value' => 'Створюю витончені прикраси ручної роботи зі срібла, поєднуючи мінімалістичні форми з природною красою натурального каміння. Кожен виріб виготовляється індивідуально з увагою до текстури, світла та природного характеру каменів.</br></br>Дизайн базується на ідеї природної гармонії, стриманої елегантності та позачасової естетики — прикраси, які відчуваються особистими, а не масовими.</br></br>Натхненням слугують природні форми та матеріали, де кожен камінь має власну унікальну структуру й відтінок. Завдяки цьому кожна прикраса стає трохи особливою та перетворюється на маленький об’єкт мистецтва, який можна носити щодня або у важливі моменти.',
        ),
    ),
    'location' => array(
        array(
            array(
                'param'    => 'block',
                'operator' => '==',
                'value'    => 'acf/author-block',
            ),
        ),
    ),
));