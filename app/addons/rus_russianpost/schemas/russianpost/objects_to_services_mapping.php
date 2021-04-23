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

$object_to_services_mapping = array(
    '3000' => array(
        57, // Crossservice
    ),
    '3010' => array(
        57, // Crossservice
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '3020' => array(
        57, // Crossservice
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
        22, // Check if the items comply with the list of postal transfers
        23, // Compliance of the declared value with the list of postal transfers
    ),
    '3040' => array(
        57, // Crossservice
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
        22, // Check if the items comply with the list of postal transfers
        23, // Compliance of the declared value with the list of postal transfers
    ),
    '16010' => array(
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '16020' => array(
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
        22, // Check if the items comply with the list of postal transfers
        23, // Compliance of the declared value with the list of postal transfers
    ),
    '16040' => array(
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
        22, // Check if the items comply with the list of postal transfers
        23, // Compliance of the declared value with the list of postal transfers
    ),

    '27030' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '27020' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '27040' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '29030' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '29020' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '29040' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '28030' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '28020' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '28040' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '4030' => array(
        12, // Oversize
        4, // Careful shipping
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '4020' => array(
        12, // Oversize
        4, // Careful shipping
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '4040' => array(
        12, // Oversize
        4, // Careful shipping
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '47030' => array(
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '47020' => array(
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '47040' => array(
        1, // Simple notification
        2, // Registered notification
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '23030' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '23020' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '23040' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '24030' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
        38, // Check for completeness
    ),
    '24020' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
        38, // Check for completeness
    ),
    '24040' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
        38, // Check for completeness
    ),
    '30030' => array(
        // no services available
    ),
    '30020' => array(
        // no services available
    ),
    '31030' => array(
        // no services available
    ),
    '31020' => array(
        // no services available
    ),

    '7030' => array(
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '7020' => array(
        14, // Insurance shipping
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '7040' => array(
        14, // Insurance
        24, // Cash on delivery by the sender
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '34030' => array(
        26, // Delivery by a courier
        27, // Russian Post Package
        28, // Russian Post's corporate client
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '34020' => array(
        26, // Delivery by a courier
        27, // Russian Post Package
        28, // Russian Post's corporate client
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '34040' => array(
        26, // Delivery by a courier
        27, // Russian Post Package
        28, // Russian Post's corporate client
        41, // SMS-notification for a sender about collecting an individual package
        42, // SMS-notification for an addressee about collecting an individual package
        43, // SMS-notification for a sender about collecting a parcel in lots
        44, // SMS-notification for an addresse about collecting a parcel in lots
    ),
    '41030' => array(
        // no services available
    ),
    '41020' => array(
        // no services available
    ),
    '41040' => array(
        // no services available
    ),

    '3001' => array(
        7, // Delivery by hand
        8, // Deliver personally
        39, // Return request; request for an address change
    ),
    '3011' => array(
        1, // Simple notification
        7, // Delivery by hand
        8, // Deliver personally
        39, // Return request; request for an address change
    ),
    '4031' => array(
        1, // Simple notification
        6, // Cumbersome parcel
        4, // Careful shipping
        7, // Delivery by hand
        8, // Deliver personally
        39, // Return request; request for an address change
    ),
    '4021' => array(
        1, // Simple notification
        6, // Cumbersome parcel
        4, // Careful shipping
        7, // Delivery by hand
        8, // Deliver personally
        39, // Return request; request for an address change
        22, // Check if the items comply with the list of postal transfers
        23, // Compliance of the declared value with the list of postal transfers
    ),
    '4041' => array(
        1, // Simple notification
        6, // Cumbersome parcel
        4, // Careful shipping
        7, // Delivery by hand
        8, // Deliver personally
        39, // Return request; request for an address change
        22, // Check if the items comply with the list of postal transfers
        23, // Compliance of the declared value with the list of postal transfers
    ),
    '7031' => array(
        10, // Product delivery
        9, // Document delivery
    ),
    '5001' => array(
        7, // Delivery by hand
        8, // Deliver personally
    ),
    '5011' => array(
        1, // Simple notification
        7, // Delivery by hand
        8, // Deliver personally
    ),
    '9001' => array(
        7, // Delivery by hand
        8, // Deliver personally
        39, // Return request; request for an address change
    ),
    '9011' => array(
        1, // Simple notification
        7, // Delivery by hand
        8, // Deliver personally
        39, // Return request; request for an address change
    ),
);

return $object_to_services_mapping;
