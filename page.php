<?php
require __DIR__ . '/bootstrap.php';
$pages = [
    'post-listing'=>['Post a listing','Make room for someone new.','Posting a home is coming soon.'],
    'about'=>['About HomeSC','A place for every student.','HomeSC is a student housing project built around finding a home near USC. More about our project is coming soon.'],
    'neighborhoods'=>['Neighborhoods','Get to know your next neighborhood.','Our neighborhood guide is coming soon.'],
    'how-it-works'=>['How it works','Your next home starts here.','A guide to finding, comparing and saving homes is coming soon.'],
    'housing-guide'=>['Housing guide','Feel at home with the details.','Our student housing guide is coming soon.'],
    'safety'=>['Safety tips','A little more peace of mind.','Our safety resources are coming soon.'],
    'help'=>['Help center','A little help finding home.','Our help center is coming soon.'],
    'privacy'=>['Privacy','Your privacy matters.','The privacy policy will be added before accounts become available.'],
    'terms'=>['Terms','The details, clearly laid out.','Our terms will be added before accounts become available.'],
];
$section = request_text($_GET, 'section', 'about');
if (!isset($pages[$section])) { http_response_code(404); $pages[$section] = ['Page not found','Let’s find your way home.','The page you requested is not available.']; }
[$eyebrow, $title, $description] = $pages[$section];
require __DIR__ . '/placeholder.php';
