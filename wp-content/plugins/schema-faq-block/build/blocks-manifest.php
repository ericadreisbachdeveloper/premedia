<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/schema-faq-block',
		'version' => '0.1.0',
		'title' => 'Schema FAQ Block',
		'category' => 'widgets',
		'description' => 'FAQ block with JSON-LD Schema markup',
		'attributes' => array(
			'faqs' => array(
				'type' => 'array',
				'default' => array(
					
				),
				'items' => array(
					'type' => 'object',
					'properties' => array(
						'question' => array(
							'type' => 'string'
						),
						'answer' => array(
							'type' => 'string'
						)
					)
				)
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true
		),
		'editorScript' => 'file:./index.js'
	)
);
