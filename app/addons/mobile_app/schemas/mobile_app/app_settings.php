<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

$schema = array(
    'images'      => array(
        'icon'                    => array(
            'name'         => 'm_app_icon',
            'type'         => 'm_app_icon',
            'image_params' => array(
                'name' => 'icon',
            ),
        ),
        'store_logo'       => array(
            'name'         => 'm_app_store_logo',
            'type'         => 'm_app_store_logo',
            'image_params' => array(
                'skip_resize' => true, // skip resizing (this image goes as link)
            ),
        ),
        'splash_screen_portrait'  => array(
            'name'         => 'm_app_splash_portrait',
            'type'         => 'm_app_splash_portrait',
            'image_params' => array(
                'name'        => 'splash_screen', // corresponds to index inside the "image_sizes" array
                'orientation' => 'portrait', // corresponds to index inside the "image_sizes" array's item (ex. 'ldpi' => 'portrait')
            ),
        ),
        'splash_screen_landscape' => array(
            'name'         => 'm_app_splash_landscape',
            'type'         => 'm_app_splash_landscape',
            'image_params' => array(
                'name'        => 'splash_screen',
                'orientation' => 'landscape',
            ),
        ),
    ),
    'image_sizes' => array(
        'android' => array( // android specific resizing settings
            'splash_screen' => array(
                'file_name'   => 'splash',
                'paths'       => array(
                    'portrait'  => array(
                        'path' => array(
                            'original_path' => 'android/app/src/main/res/drawable', // the path where original image (without resizing) goes
                            'path'          => 'android/app/src/main/res/drawable-%resolution_code%', // the path where the resized image goes, the %placefolder% should be replaced with
                            'variables'     => array('resolution_code'), // the variables to replace inside the path
                        ),
                    ),
                    'landscape' => array(
                        'path' => array(
                            'original_path' => 'android/app/src/main/res/drawable-land',
                            'path'          => 'android/app/src/main/res/drawable-land-%resolution_code%',
                            'variables'     => array('resolution_code'),
                        ),
                    ),
                ),
                'resolutions' => array(
                    'original'    => array(
                        'portrait'  => array(
                            'width'  => 480,
                            'height' => 800,
                        ),
                        'landscape' => array(
                            'width'  => 800,
                            'height' => 480,
                        ),
                    ),
                    'ldpi'    => array(
                        'portrait'  => array(
                            'width'  => 200,
                            'height' => 320,
                        ),
                        'landscape' => array(
                            'width'  => 320,
                            'height' => 200,
                        ),
                    ),
                    'mdpi'    => array(
                        'portrait'  => array(
                            'width'  => 320,
                            'height' => 480,
                        ),
                        'landscape' => array(
                            'width'  => 480,
                            'height' => 320,
                        ),
                    ),
                    'hdpi'    => array(
                        'portrait'  => array(
                            'width'  => 480,
                            'height' => 800,
                        ),
                        'landscape' => array(
                            'width'  => 800,
                            'height' => 480,
                        ),
                    ),
                    'xhdpi'   => array(
                        'portrait'  => array(
                            'width'  => 720,
                            'height' => 1280,
                        ),
                        'landscape' => array(
                            'width'  => 1280,
                            'height' => 720,
                        ),
                    ),
                    'xxhdpi'  => array(
                        'portrait'  => array(
                            'width'  => 960,
                            'height' => 1600,
                        ),
                        'landscape' => array(
                            'width'  => 1600,
                            'height' => 960,
                        ),
                    ),
                    'xxxhdpi' => array(
                        'portrait'  => array(
                            'width'  => 1280,
                            'height' => 1920,
                        ),
                        'landscape' => array(
                            'width'  => 1920,
                            'height' => 1280,
                        ),
                    ),
                ),
            ),
            'icon'          => array(
                'file_name'   => 'icon',
                'paths'       => array(
                    array(
                        'original_path' => 'android/app/src/main/res/drawable',
                        'path'          => 'android/app/src/main/res/drawable-%resolution_code%',
                        'variables'     => array('resolution_code'),
                    ),
                ),
                'resolutions' => array(
                    'original'    => array(
                        'width'  => 96,
                        'height' => 96,
                    ),
                    'ldpi'    => array(
                        'width'  => 36,
                        'height' => 36,
                    ),
                    'mdpi'    => array(
                        'width'  => 48,
                        'height' => 48,
                    ),
                    'tvdpi'   => array(
                        'width'  => 64,
                        'height' => 64,
                    ),
                    'hdpi'    => array(
                        'width'  => 72,
                        'height' => 72,
                    ),
                    'xhdpi'   => array(
                        'width'  => 96,
                        'height' => 96,
                    ),
                    'xxhdpi'  => array(
                        'width'  => 144,
                        'height' => 144,
                    ),
                    'xxxhdpi' => array(
                        'width'  => 192,
                        'height' => 192,
                    ),
                ),
            ),
        ),
        'ios'     => array(
            'icon'          => array(
                'name'        => array(
                    'file_name' => 'Icon-App-%width%x%height%@%scale%x',
                    'variables' => array('width', 'height', 'scale'),
                ),
                'path'        => 'ios/csnative/Images.xcassets/AppIcon.appiconset',
                'resolutions' => array(
                    array(
                        'width'  => 20,
                        'height' => 20,
                        'scales' => array(
                            1 => array('ipad'),
                            2 => array('ipad', 'iphone'),
                            3 => array('iphone'),
                        ),
                    ),
                    array(
                        'width'  => 29,
                        'height' => 29,
                        'scales' => array(
                            1 => array('ipad', 'iphone'),
                            2 => array('ipad', 'iphone'),
                            3 => array('iphone'),
                        ),
                    ),
                    array(
                        'width'  => 40,
                        'height' => 40,
                        'scales' => array(
                            1 => array('ipad', 'iphone'),
                            2 => array('ipad', 'iphone'),
                            3 => array('iphone'),
                        ),
                    ),
                    array(
                        'width'  => 57,
                        'height' => 57,
                        'scales' => array(
                            1 => array('iphone'),
                            2 => array('iphone'),
                        ),
                    ),
                    array(
                        'width'  => 60,
                        'height' => 60,
                        'scales' => array(
                            1 => array('iphone'),
                            2 => array('iphone'),
                            3 => array('iphone'),
                        ),
                    ),
                    array(
                        'width'  => 72,
                        'height' => 72,
                        'scales' => array(
                            1 => array('ipad'),
                            2 => array('ipad'),
                        ),
                    ),
                    array(
                        'width'  => 76,
                        'height' => 76,
                        'scales' => array(
                            1 => array('iphone'),
                            2 => array('ipad'),
                            3 => array('ipad'),
                        ),
                    ),
                    array(
                        'width'  => 83.5,
                        'height' => 83.5,
                        'scales' => array(
                            2 => array('ipad'),
                        ),
                    ),
                    array(
                        'width'  => 50,
                        'height' => 50,
                        'scales' => array(
                            1 => array('ipad'),
                            2 => array('ipad'),
                        ),
                        'name'   => array(
                            'file_name' => 'Icon-Small-%width%x%height%@%scale%x',
                            'variables' => array('width', 'height', 'scale'),
                        ),
                    ),
                    array(
                        'width' => 1024,
                        'height' => 1024,
                        'scales' => array(
                            1 => array('ipad'),
                        ),
                        'name' => array(
                            'file_name' => 'ItunesArtwork@2x'
                        ),
                    ),
                ),
            ),
            'splash_screen' => array(
                'path'        => 'ios/csnative/Images.xcassets/LaunchImage.launchimage',
                'resolutions' => array(
                    'landscape' => array(
                        array(
                            'width'  => 2208, // real size 2048 changed to the value from name
                            'height' => 1242,
                            'name'   => 'landscape_2208x1242',
                        ),
                        array(
                            'width'  => 1334,
                            'height' => 750,
                            'name'   => 'landscape_1334x750',
                        ),
                        array(
                            'width'  => 2048,
                            'height' => 1496,
                            'name'   => 'landscape_2048x1496',
                        ),
                        array(
                            'width'  => 2048,
                            'height' => 1536,
                            'name'   => 'landscape_2048x1536',
                        ),
                        array(
                            'width'  => 1024,
                            'height' => 748,
                            'name'   => 'landscape_1024x748',
                        ),
                        array(
                            'width'  => 1024,
                            'height' => 768,
                            'name'   => 'landscape_1024x768',
                        ),
                        array(
                            'width'  => 2688,
                            'height' => 1242,
                            'name'   => 'landscape_2688x1242',
                        ),
                        array(
                            'width'  => 2436,
                            'height' => 1125,
                            'name'   => 'landscape_2436x1125',
                        ),
                        array(
                            'width'  => 1792,
                            'height' => 828,
                            'name'   => 'landscape_1792x828',
                        ),
                        array(
                            'width'  => 2436,
                            'height' => 1125,
                            'name'   => 'landscape_2436x1125',
                        ),
                        array(
                            'width'  => 1136,
                            'height' => 640,
                            'name'   => 'landscape_1136x640',
                        ),
                    ),
                    'portrait'  => array(
                        array(
                            'width'  => 1242,
                            'height' => 2208, // real size 2048 changed to the value from name
                            'name'   => 'portrait_1242x2208',
                        ),
                        array(
                            'width'  => 750,
                            'height' => 1334,
                            'name'   => 'portrait_750x1334',
                        ),
                        array(
                            'width'  => 1536,
                            'height' => 2008,
                            'name'   => 'portrait_1536x2008',
                        ),
                        array(
                            'width'  => 1536,
                            'height' => 2048,
                            'name'   => 'portrait_1536x2048',
                        ),
                        array(
                            'width'  => 768,
                            'height' => 1024,
                            'name'   => 'portrait_768x1024',
                        ),
                        array(
                            'width'  => 768,
                            'height' => 1004,
                            'name'   => 'portrait_768x1004',
                        ),
                        array(
                            'width'  => 640,
                            'height' => 960,
                            'name'   => 'portrait_640x960',
                        ),
                        array(
                            'width'  => 1536,
                            'height' => 2008,
                            'name'   => 'portrait_1536x2008',
                        ),
                        array(
                            'width'  => 320,
                            'height' => 480,
                            'name'   => 'portrait_320x480',
                        ),
                        array(
                            'width'  => 640,
                            'height' => 1136,
                            'name'   => 'portrait_640x1136',
                        ),
                        array(
                            'width'  => 1242,
                            'height' => 2688,
                            'name'   => 'portrait_1242x2688',
                        ),
                        array(
                            'width'  => 1125,
                            'height' => 2436,
                            'name'   => 'portrait_1125x2436',
                        ),
                        array(
                            'width'  => 828,
                            'height' => 1792,
                            'name'   => 'portrait_828x1792',
                        ),
                        array(
                            'width'  => 1125,
                            'height' => 2436,
                            'name'   => 'portrait_1125x2436',
                        ),
                    ),
                ),
            ),
        ),
    ),
);

return $schema;
