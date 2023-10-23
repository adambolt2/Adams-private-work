<?php
session_start();
require_once('./config_members.php');
$serv = $config["host"];
$us = $config["user"];
$wrd = $config["password"];
$nmedb = $config["dbname"];

$servername = $serv;
$username = $us;
$password = $wrd;
$dbname = $nmedb;


// Check if the token is set in the query parameters
if (isset($_GET["token"])) {
    $tokenFromURL = $_GET["token"];
 //   echo $_SESSION["ProfileTokens"] ;
   // echo "<br>";
    //echo $tokenFromURL;    
    // Check if the token matches the one stored in the session
    if ($tokenFromURL === $_SESSION["ProfileTokens"]) {
        // Token is valid, allow access to the profile
    //    echo "Access granted to the profile.";
        // Implement your profile logic here.
    } else {
        // Invalid token, deny access
        echo "Invalid token. Access denied.";
        header("Location: index.php");
        exit;
        // Implement your error handling logic here.
    }
} else {
    // Token not provided, handle accordingly
    echo "Token not provided. Access denied.";
    // Implement your error handling logic here.
}



// not need at view 
$user_email = $_SESSION['LoginMail']; 


$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Prepare the SQL query to select all emails from the four user type tables
$the_membership_type = array("contributor", "associate", "buddy", "entrepreneur");
$number_of_user_type_tables = 4;
$all_emails = array();
$email_membership_map = array(); // Initialize the map to store email and membership type

for ($n = 0; $n < $number_of_user_type_tables; $n++) {
    $membership_type = $the_membership_type[$n];
    $sql = "SELECT email_address FROM {$membership_type}";
    
    $result = mysqli_query($conn, $sql) or die("Couldn't execute the query");

    while ($row = mysqli_fetch_assoc($result)) {
        $email = $row['email_address'];
        // Store the email and its corresponding membership type in the map
        $email_membership_map[$email] = $membership_type;
        $all_emails[] = $email;
    }
}

// Close the database connection


// Check if the email exists in the array of all emails
if (in_array($user_email, $all_emails)) {
    $membership_type = $email_membership_map[$user_email];
    $first_name = '';
    $given_name = '';
    $personal_image_path = ''; // Initialize personal image path




    // this is gonna be for all info in db 
    $biz_type = '';
    $biz_name = '';
    $biz_cat = '';
    $biz_desc = '';
    $Ent_experience= '';
    $Ent_expertise =''; 
    $Ent_highest_qual = '';
  
    $bud_business_type = '';
    $bud_experience = '';
    $bud_proposal = '';
    $bud_qual = '';
    $bud_expert = '';
    $bud_wants = '';
    $Uniqueid = '';


    $cont_type = '';
    $cont_org_name = '';
    // might need to make a new sql as well :(

    if ($membership_type === "entrepreneur") {
        $sql = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image FROM entrepreneur WHERE email_address = ?";
        $Infosql = "SELECT ent_operate_as, ent_organisation_name,ent_member_biz_category,ent_member_description,ent_experience,ent_expertise,ent_highest_qualification
         FROM entrepreneur WHERE email_address = ?";
        // replace email with unique id of the viewed user when in view_profile
       
       
       
        // this will be deleted in viewprofile not needed
        $UniqueSql = "SELECT unique_id FROM entrepreneur WHERE email_address = ?";
         
        


        $stmt5 = $conn->prepare($UniqueSql);
        $stmt5->bind_param("s", $user_email);
        $stmt5->execute();
        $stmt5->bind_result($Uniqueid);
        $stmt5->fetch();
        $stmt5->close();





        $_SESSION['UniqueID'] = $Uniqueid;
// this gets to be the viewed profiles uniqueid rather than this , wont need to get user_id through their email
        


        $stmt = $conn->prepare($Infosql);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->bind_result($biz_type,$biz_name,$biz_cat,$biz_desc,$Ent_experience,$Ent_expertise,$Ent_highest_qual);
        $stmt->fetch();
        $stmt->close();
     //   echo $biz_type,$biz_name,$biz_cat,$biz_desc,$Ent_experience,$Ent_expertise,$Ent_highest_qual;
        






       // This is before the storm below :)
       
        $_SESSION["Member"] = "entrepreneur";

// Prepare the SQL query using a prepared statement
$testSql = "SELECT
in_place_people_technical,
in_place_people_commercial,
in_place_people_general_labour,
in_place_constitution,
in_place_assets_property,
in_place_assets_equipment,
in_place_assets_other,
in_place_assets_tools,
in_place_workshop_factory_unit,
in_place_office_other_type_building,
in_place_product_part_built_system,
in_place_land,
in_place_asset_air_craft,
in_place_asset_boat_ship,
in_place_asset_physical_other,
in_place_research_accumulated,
in_place_some_capital,
in_place_experience_expertise,
in_place_investors,
in_place_contracts_one_or_more,
in_place_agent,
in_place_completed_system_product,
in_place_requirements,
in_place_design_drawing,
in_place_prototype_MVP,
in_place_registered_incorporated,
in_place_turnover_les250k,
in_place_turnoverbtw250_500k,
in_place_turnovergt500k,
in_place_turnovergt1Mil,
in_place_none_of_above
FROM entrepreneur
WHERE email_address = ?";

// STATEMENT 2 FOR WHAT THEY HAVE IN PLACE

$stmt2 = $conn->prepare($testSql);

if ($stmt2) {
// Bind the email address parameter
$stmt2->bind_param("s", $user_email);

// Execute the statement
$stmt2->execute();

// Bind the result columns to variables
$stmt2->bind_result(
$ent_inplace_people_technical,
$ent_inplace_people_commercial,
$ent_inplace_people_general_labour,
$ent_inplace_constitution,
$ent_inplace_assets_property,
$ent_inplace_assets_equipment,
$ent_inplace_assets_other,
$ent_inplace_assets_tools,
$ent_inplace_workshop_factory_unit,
$ent_inplace_office_other_type_building,
$ent_inplace_product_part_built_system,
$ent_inplace_land,
$ent_inplace_asset_air_craft,
$ent_inplace_asset_boat_ship,
$ent_inplace_asset_physical_other,
$ent_inplace_research_accumulated,
$ent_inplace_some_capital,
$ent_inplace_experience_expertise,
$ent_inplace_investors,
$ent_inplace_contracts_one_or_more,
$ent_inplace_agent,
$ent_inplace_completed_system_product,
$ent_inplace_requirements,
$ent_inplace_design_drawing,
$ent_inplace_prototype_MVP,
$ent_inplace_registered_incorporated,
$ent_inplace_turnover_les250k,
$ent_inplace_turnoverbtw250_500k,
$ent_inplace_turnovergt500k,
$ent_inplace_turnovergt1Mil,
$ent_inplace_none_of_above
);

if ($stmt2->fetch()) {
// You've fetched a row
// Create an associative array excluding null values
$entInplaceArray = [
    'in_place_people_technical' => $ent_inplace_people_technical,
    'in_place_people_commercial' => $ent_inplace_people_commercial,
    'in_place_people_general_labour' => $ent_inplace_people_general_labour,
    'in_place_constitution' => $ent_inplace_constitution,
    'in_place_assets_property' => $ent_inplace_assets_property,
    'in_place_assets_equipment' => $ent_inplace_assets_equipment,
    'in_place_assets_other' => $ent_inplace_assets_other,
    'in_place_assets_tools' => $ent_inplace_assets_tools,
    'in_place_workshop_factory_unit' => $ent_inplace_workshop_factory_unit,
    'in_place_office_other_type_building' => $ent_inplace_office_other_type_building,
    'in_place_product_part_built_system' => $ent_inplace_product_part_built_system,
    'in_place_land' => $ent_inplace_land,
    'in_place_asset_air_craft' => $ent_inplace_asset_air_craft,
    'in_place_asset_boat_ship' => $ent_inplace_asset_boat_ship,
    'in_place_asset_physical_other' => $ent_inplace_asset_physical_other,
    'in_place_research_accumulated' => $ent_inplace_research_accumulated,
    'in_place_some_capital' => $ent_inplace_some_capital,
    'in_place_experience_expertise' => $ent_inplace_experience_expertise,
    'in_place_investors' => $ent_inplace_investors,
    'in_place_contracts_one_or_more' => $ent_inplace_contracts_one_or_more,
    'in_place_agent' => $ent_inplace_agent,
    'in_place_completed_system_product' => $ent_inplace_completed_system_product,
    'in_place_requirements' => $ent_inplace_requirements,
    'in_place_design_drawing' => $ent_inplace_design_drawing,
    'in_place_prototype_MVP' => $ent_inplace_prototype_MVP,
    'in_place_registered_incorporated' => $ent_inplace_registered_incorporated,
    'in_place_turnover_les250k' => $ent_inplace_turnover_les250k,
    'in_place_turnoverbtw250_500k' => $ent_inplace_turnoverbtw250_500k,
    'in_place_turnovergt500k' => $ent_inplace_turnovergt500k,
    'in_place_turnovergt1Mil' => $ent_inplace_turnovergt1Mil,
    'in_place_none_of_above' => $ent_inplace_none_of_above,
];

$entInplaceArray = array_filter($entInplaceArray, function ($value) {
return $value !== null;
});

// Do something with the retrieved values
foreach ($entInplaceArray as $columnName => $columnValue) {
  //  echo " $columnValue<br>";
    // this is for testing will be used in html later :) btw, this is disguisting :)
}

// Print the array for demonstration
} else {
//echo "No rows found.";
}

// Close the statement
$stmt2->close();
} else {
echo "Statement preparation failed: " . $conn->error;
}

// Close the connection
// Time for statement 3 :))))))))

$buddyRETSql = "SELECT
                budy_people_technical_expertise,
                budy_people_financial_expertise,
                budy_people_accountancy_expertise,
                budy_people_marketing_expertise,
                budy_people_general_labour_other,
                budy_people_legal_expertise,
                budy_people_business_admin_expertise,
                budy_people_modelling,
                budy_people_patents_expertise,
                budy_assets_property,
                budy_assets_laboratory,
                budy_assets_tools,
                budy_land,
                budy_service_transport,
                budy_asset_boat_aeroplane_vehicles,
                budy_asset_physical_other,
                budy_research_accumulated,
                budy_workshop_factory_space,
                budy_office_other_building,
                budy_assets_machinery,
                budy_finance_investment,
                budy_outlets,
                budy_shop_space,
                budy_dry_dock,
                budy_contracts_one_or_more,
                budy_agent,
                budy_none
            FROM entrepreneur
            WHERE email_address = ?";

$stmt3 = $conn->prepare($buddyRETSql);

if ($stmt3) {
    // Bind the email address parameter
    $stmt3->bind_param("s", $user_email);

    // Execute the statement
    $stmt3->execute();

    // Bind the result columns to variables
    $stmt3->bind_result(
        $budy_people_technical_expertise,
        $budy_people_financial_expertise,
        $budy_people_accountancy_expertise,
        $budy_people_marketing_expertise,
        $budy_people_general_labour_other,
        $budy_people_legal_expertise,
        $budy_people_business_admin_expertise,
        $budy_people_modelling,
        $budy_people_patents_expertise,
        $budy_assets_property,
        $budy_assets_laboratory,
        $budy_assets_tools,
        $budy_land,
        $budy_service_transport,
        $budy_asset_boat_aeroplane_vehicles,
        $budy_asset_physical_other,
        $budy_research_accumulated,
        $budy_workshop_factory_space,
        $budy_office_other_building,
        $budy_assets_machinery,
        $budy_finance_investment,
        $budy_outlets,
        $budy_shop_space,
        $budy_dry_dock,
        $budy_contracts_one_or_more,
        $budy_agent,
        $budy_none
    );

    if ($stmt3->fetch()) {
        // Create an associative array excluding null values
        $buddyRetArray = [
            'budy_people_technical_expertise' => $budy_people_technical_expertise,
            'budy_people_financial_expertise' => $budy_people_financial_expertise,
            'budy_people_accountancy_expertise' => $budy_people_accountancy_expertise,
            'budy_people_marketing_expertise' => $budy_people_marketing_expertise,
            'budy_people_general_labour_other' => $budy_people_general_labour_other,
            'budy_people_legal_expertise' => $budy_people_legal_expertise,
            'budy_people_business_admin_expertise' => $budy_people_business_admin_expertise,
            'budy_people_modelling' => $budy_people_modelling,
            'budy_people_patents_expertise' => $budy_people_patents_expertise,
            'budy_assets_property' => $budy_assets_property,
            'budy_assets_laboratory' => $budy_assets_laboratory,
            'budy_assets_tools' => $budy_assets_tools,
            'budy_land' => $budy_land,
            'budy_service_transport' => $budy_service_transport,
            'budy_asset_boat_aeroplane_vehicles' => $budy_asset_boat_aeroplane_vehicles,
            'budy_asset_physical_other' => $budy_asset_physical_other,
            'budy_research_accumulated' => $budy_research_accumulated,
            'budy_workshop_factory_space' => $budy_workshop_factory_space,
            'budy_office_other_building' => $budy_office_other_building,
            'budy_assets_machinery' => $budy_assets_machinery,
            'budy_finance_investment' => $budy_finance_investment,
            'budy_outlets' => $budy_outlets,
            'budy_shop_space' => $budy_shop_space,
            'budy_dry_dock' => $budy_dry_dock,
            'budy_contracts_one_or_more' => $budy_contracts_one_or_more,
            'budy_agent' => $budy_agent,
            'budy_none' => $budy_none,
        ];
        
        // Remove null values from the array
        $buddyRetArray = array_filter($buddyRetArray, function ($value) {
            return $value !== null;
        });

        // Do something with the retrieved values
        foreach ($buddyRetArray as $columnName => $columnValue) {
        //    echo " $columnValue<br>";
            // this is for testing will be used in html later :) btw, this is disguisting :)
        } // echo the array for demonstration Have i mentioned how much i hate this
    } else {
       // echo "No rows found.";
    }

    // Close the statement
    $stmt3->close();
} else {
    echo "Statement preparation failed: " . $conn->error;
}

        
    } elseif ($membership_type === "buddy") {
        $sql = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE email_address = ?";
        $Infosql = "SELECT  bud_business_type,bud_experience,bud_proposal_description,bud_highest_achieved_qual,bud_expertise,bud_want_from_entreprenur
        FROM buddy WHERE email_address = ?";
// replace email with unique id of the viewed user when in view_profile
  


// not neeeded     
    $UniqueSql = "SELECT unique_id FROM buddy WHERE email_address = ?";
         
        


$stmt5 = $conn->prepare($UniqueSql);
$stmt5->bind_param("s", $user_email);
$stmt5->execute();
$stmt5->bind_result($Uniqueid);
$stmt5->fetch();
$stmt5->close();






$_SESSION['UniqueID'] = $Uniqueid;
// not needed

              
       $stmt = $conn->prepare($Infosql);
       $stmt->bind_param("s", $user_email);
       $stmt->execute();
       $stmt->bind_result($bud_business_type,$bud_experience,$bud_proposal,$bud_qual,$bud_expert,$bud_wants);
       $stmt->fetch();
       $stmt->close();
       echo  $bud_business_type, $bud_experience,$bud_proposal,$bud_qual,$bud_expert,$bud_wants;
       

         echo $user_email;
        
        
        
        $_SESSION["Member"] = "buddy";


// This is for assist1 & 2 , as u can see its horrible :))))

                $buddyAssistance = "SELECT
                  accountancy,
                  adventure,
                  advertisement_marketing_public_relations,
                  agriculture_farming,
                  alternative_energy,
                  alternative_medicine,
                  animals_veterinary,
                  architecture,
                  automotive,
                  aviation_aeronautical,
                  banking,
                  beauty_perfume,
                  beverages,
                  biological,
                  building_construction_fabrication,
                  cafe,
                  charity,
                  clothing,
                  communications,
                  computer_services_hardware,
                  computer_services_software,
                  cottage_industries_souvenirs,
                  cruise_trips_and_travel,
                  edible_products,
                  electrical_electronics,
                  engineering_chemical,
                  engineering_civil,
                  engineering_mechanical,
                  exploration_or_drilling,
                  financial_services_investment_insurance,
                  gambling,
                  hair,
                  hotel_bed_and_breakfast,
                  housing_goods,
                  housing,
                  jewelry_precious_metals_watches,
                  legal_services,
                  leisure,
                  machinery,
                  marine,
                  medicine,
                  mining,
                  mobile_technology,
                  music,
                  natural_materials,
                  opticians,
                  optics,
                  other_services,
                  other_wearable_items,
                  pharmaceutical,
                  publishing,
                  renewable_energy,
                  robotics,
                  rubber_plastics,
                  safety,
                  shipping,
                  something_else,
                  transportation
                FROM buddy
                WHERE email_address = ?";

$stmt2 = $conn->prepare($buddyAssistance);

if ($stmt2) {
    // Bind the email address parameter
    $stmt2->bind_param("s", $user_email);

    // Execute the statement
    $stmt2->execute();

    // Bind the result columns to variables
    $stmt2->bind_result(
        $accountancy,
        $adventure,
        $advertisement_marketing_public_relations,
        $agriculture_farming,
        $alternative_energy,
        $alternative_medicine,
        $animals_veterinary,
        $architecture,
        $automotive,
        $aviation_aeronautical,
        $banking,
        $beauty_perfume,
        $beverages,
        $biological,
        $building_construction_fabrication,
        $cafe,
        $charity,
        $clothing,
        $communications,
        $computer_services_hardware,
        $computer_services_software,
        $cottage_industries_souvenirs,
        $cruise_trips_and_travel,
        $edible_products,
        $electrical_electronics,
        $engineering_chemical,
        $engineering_civil,
        $engineering_mechanical,
        $exploration_or_drilling,
        $financial_services_investment_insurance,
        $gambling,
        $hair,
        $hotel_bed_and_breakfast,
        $housing_goods,
        $housing,
        $jewelry_precious_metals_watches,
        $legal_services,
        $leisure,
        $machinery,
        $marine,
        $medicine,
        $mining,
        $mobile_technology,
        $music,
        $natural_materials,
        $opticians,
        $optics,
        $other_services,
        $other_wearable_items,
        $pharmaceutical,
        $publishing,
        $renewable_energy,
        $robotics,
        $rubber_plastics,
        $safety,
        $shipping,
        $something_else,
        $transportation
    );

    if ($stmt2->fetch()) {
        // Create an associative array excluding null values
        $buddyAssistanceArray = [
            'accountancy' => $accountancy,
            'adventure' => $adventure,
            'advertisement_marketing_public_relations' => $advertisement_marketing_public_relations,
            'agriculture_farming' => $agriculture_farming,
            'alternative_energy' => $alternative_energy,
            'alternative_medicine' => $alternative_medicine,
            'animals_veterinary' => $animals_veterinary,
            'architecture' => $architecture,
            'automotive' => $automotive,
            'aviation_aeronautical' => $aviation_aeronautical,
            'banking' => $banking,
            'beauty_perfume' => $beauty_perfume,
            'beverages' => $beverages,
            'biological' => $biological,
            'building_construction_fabrication' => $building_construction_fabrication,
            'cafe' => $cafe,
            'charity' => $charity,
            'clothing' => $clothing,
            'communications' => $communications,
            'computer_services_hardware' => $computer_services_hardware,
            'computer_services_software' => $computer_services_software,
            'cottage_industries_souvenirs' => $cottage_industries_souvenirs,
            'cruise_trips_and_travel' => $cruise_trips_and_travel,
            'edible_products' => $edible_products,
            'electrical_electronics' => $electrical_electronics,
            'engineering_chemical' => $engineering_chemical,
            'engineering_civil' => $engineering_civil,
            'engineering_mechanical' => $engineering_mechanical,
            'exploration_or_drilling' => $exploration_or_drilling,
            'financial_services_investment_insurance' => $financial_services_investment_insurance,
            'gambling' => $gambling,
            'hair' => $hair,
            'hotel_bed_and_breakfast' => $hotel_bed_and_breakfast,
            'housing_goods' => $housing_goods,
            'housing' => $housing,
            'jewelry_precious_metals_watches' => $jewelry_precious_metals_watches,
            'legal_services' => $legal_services,
            'leisure' => $leisure,
            'machinery' => $machinery,
            'marine' => $marine,
            'medicine' => $medicine,
            'mining' => $mining,
            'mobile_technology' => $mobile_technology,
            'music' => $music,
            'natural_materials' => $natural_materials,
            'opticians' => $opticians,
            'optics' => $optics,
            'other_services' => $other_services,
            'other_wearable_items' => $other_wearable_items,
            'pharmaceutical' => $pharmaceutical,
            'publishing' => $publishing,
            'renewable_energy' => $renewable_energy,
            'robotics' => $robotics,
            'rubber_plastics' => $rubber_plastics,
            'safety' => $safety,
            'shipping' => $shipping,
            'something_else' => $something_else,
            'transportation' => $transportation,
        ];
        
        // Remove null values from the array
        $buddyAssistanceArray = array_filter($buddyAssistanceArray, function ($value) {
            return $value !== null;
        });

        foreach ($buddyAssistanceArray as $columnName => $columnValue) {
            echo " $columnValue<br>";
            // this is for testing will be used in html later :) btw, this is disguisting :)
        }
         // echo the array for demonstration Have i mentioned how much i hate this// Print the array for demonstration
    } else {
       // echo "No rows found.";
    }

    // Close the statement
    $stmt2->close();
} else {
    echo "Statement preparation failed: " . $conn->error;
}

// Close the connection



$buddyOffer = "SELECT
offr_people_associate_technical,
offr_people_associate_commercial,
offr_people_associate_general_labour,
offr_people_associate_legal,
offr_assets_property,
offr_assets_equipment_scientific,
offr_assets_equipment_technical,
offr_assets_equipment_musical,
offr_plant_and_machinery,
offr_assets_other_equipment,
offr_assets_tools,
offr_workshop_factory_unit,
offr_office_other_type_of_building,
offr_product_part_built_product_system,
offr_land,
offr_asset_air_craft,
offr_asset_boat_ship,
offr_asset_physical_other,
offr_research_accumulated,
offr_capital,
offr_experience_expertise,
offr_investors,
offr_contracts_one_or_more,
offr_none
FROM buddy
WHERE email_address = ?";

$stmt3 = $conn->prepare($buddyOffer);

if ($stmt3) {
// Bind the email address parameter
$stmt3->bind_param("s", $user_email);

// Execute the statement
$stmt3->execute();

// Bind the result columns to variables
$stmt3->bind_result(
$offr_people_associate_technical,
$offr_people_associate_commercial,
$offr_people_associate_general_labour,
$offr_people_associate_legal,
$offr_assets_property,
$offr_assets_equipment_scientific,
$offr_assets_equipment_technical,
$offr_assets_equipment_musical,
$offr_plant_and_machinery,
$offr_assets_other_equipment,
$offr_assets_tools,
$offr_workshop_factory_unit,
$offr_office_other_type_of_building,
$offr_product_part_built_product_system,
$offr_land,
$offr_asset_air_craft,
$offr_asset_boat_ship,
$offr_asset_physical_other,
$offr_research_accumulated,
$offr_capital,
$offr_experience_expertise,
$offr_investors,
$offr_contracts_one_or_more,
$offr_none
);

if ($stmt3->fetch()) {
// Create an associative array excluding null values
$buddyOfferArray = [
'offr_people_associate_technical' => $offr_people_associate_technical,
'offr_people_associate_commercial' => $offr_people_associate_commercial,
'offr_people_associate_general_labour' => $offr_people_associate_general_labour,
'offr_people_associate_legal' => $offr_people_associate_legal,
'offr_assets_property' => $offr_assets_property,
'offr_assets_equipment_scientific' => $offr_assets_equipment_scientific,
'offr_assets_equipment_technical' => $offr_assets_equipment_technical,
'offr_assets_equipment_musical' => $offr_assets_equipment_musical,
'offr_plant_and_machinery' => $offr_plant_and_machinery,
'offr_assets_other_equipment' => $offr_assets_other_equipment,
'offr_assets_tools' => $offr_assets_tools,
'offr_workshop_factory_unit' => $offr_workshop_factory_unit,
'offr_office_other_type_of_building' => $offr_office_other_type_of_building,
'offr_product_part_built_product_system' => $offr_product_part_built_product_system,
'offr_land' => $offr_land,
'offr_asset_air_craft' => $offr_asset_air_craft,
'offr_asset_boat_ship' => $offr_asset_boat_ship,
'offr_asset_physical_other' => $offr_asset_physical_other,
'offr_research_accumulated' => $offr_research_accumulated,
'offr_capital' => $offr_capital,
'offr_experience_expertise' => $offr_experience_expertise,
'offr_investors' => $offr_investors,
'offr_contracts_one_or_more' => $offr_contracts_one_or_more,
'offr_none' => $offr_none,
];

// Remove null values from the array
$buddyOfferArray = array_filter($buddyOfferArray, function ($value) {
return $value !== null;
});

// Do something with the retrieved values
foreach ($buddyOfferArray as $columnName => $columnValue) {
    echo " $columnValue<br>";
    
    // this is for testing will be used in html later :) btw, this is disguisting :)
} // Print the array for demonstration
} else {
echo "No rows found.";
}

// Close the statement
$stmt3->close();
} else {
echo "Statement preparation failed: " . $conn->error;
}



    } elseif ($membership_type === "contributor") {
        $_SESSION["Member"] = "contributor";
        $sql = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE email_address = ?";
        $Infosql = "SELECT  contributor_type_of_organisation,contributor_organisation_name FROM contributor WHERE email_address = ?";
        // replace email with unique id of the viewed user when in view_profile
    
     // Not neeeded
        $UniqueSql = "SELECT unique_id FROM contributor WHERE email_address = ?";
         
        


        $stmt5 = $conn->prepare($UniqueSql);
        $stmt5->bind_param("s", $user_email);
        $stmt5->execute();
        $stmt5->bind_result($Uniqueid);
        $stmt5->fetch();
        $stmt5->close();




        //echo $user_email;

        $_SESSION['UniqueID'] = $Uniqueid;

 //not needed end       
 
    $stmt = $conn->prepare($Infosql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->bind_result($cont_type,  $cont_org_name );
    $stmt->fetch();
    $stmt->close();
    //echo $cont_type, $cont_org_name;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->bind_result($first_name, $given_name, $personal_image_blob);
    $stmt->fetch();
    $stmt->close();


   
    // we have these here because all 3 fields have these 3 in commin :)

}







// Now we start education display and soo on 



$selectQuery = "SELECT * FROM education WHERE unique_id = ? AND FormNum IN (1, 2, 3)";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("s", $Uniqueid);
$stmt->execute();

// Get the results
$result = $stmt->get_result();

// Create arrays to store education information
$schoolsArray = array();
$degreesArray = array();
$graduateYearsArray = array();
$majorsArray = array();
$nullFormNums = array(); // Array to store null FormNum values
// Fetch and process the results as needed
while ($row = $result->fetch_assoc()) {
    $formNum = $row["FormNum"];

    // Process each row of data here
    if ($formNum === null) {
        // Handle case where FormNum is null
    } else {
        // Store education information
        $schoolsArray[$formNum] = $row["school"];
        $degreesArray[$formNum] = $row["degree"];
        $graduateYearsArray[$formNum] = $row["graduate_year"];
        $majorsArray[$formNum] = $row["Major"];
    }
}



/* Display stored education information
for ($i = 1; $i <= 3; $i++) {
    if (isset($schoolsArray[$i])) {
        echo "FormNum: $i<br>";
        echo "School: " . $schoolsArray[$i] . "<br>";
        echo "Degree: " . $degreesArray[$i] . "<br>";
        echo "Graduate Year: " . $graduateYearsArray[$i] . "<br>";
        echo "Major: " . $majorsArray[$i] . "<br>";
        echo "<br>";
    } else {
        echo "FormNum $i not found.<br>";
        $nullFormNums[] = $i;
    }
}
 2nd form*/




$selectQuery = "SELECT * FROM employment WHERE unique_id = ? AND FormNum IN (1, 2, 3)";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("s", $Uniqueid);
$stmt->execute();

// Get the results
$result = $stmt->get_result();

// Create arrays to store employ information
$titleArray = array();
$employerArray = array();

$descriptionArray = array();
$nullFormNums2 = array(); // Array to store null FormNum values
// Fetch and process the results as needed
while ($row = $result->fetch_assoc()) {
    $formNum = $row["FormNum"];
    
    // Process each row of data here
    if ($formNum === null) {
        // Handle case where FormNum is null
    } else {
        // Store education information
       $titleArray[$formNum] = $row["title"];
        $employerArray[$formNum] = $row["employer"];
        $descriptionArray[$formNum] = $row["description"];
    }
}



// Display stored education information
/* for some reason employer does it but not title whatevrr works still
for ($i = 1; $i <= 3; $i++) {
    if (isset($employerArray[$i])) {
        echo "FormNum: $i<br>";
        echo "title: " . $titleArray[$i] . "<br>";
        echo "employer: " . $employerArray[$i] . "<br>";
        echo "description: " . $descriptionArray[$i] . "<br>";
        echo "<br>";
    } else {
        echo "FormNum $i not found.<br>";
        $nullFormNums2[] = $i;
    }
}





*/





// expertise

$selectQuery = "SELECT * FROM expertise WHERE unique_id = ? AND FormNum IN (1, 2, 3,4,5,6,7)";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("s", $Uniqueid);
$stmt->execute();

// Get the results
$result = $stmt->get_result();

// Create arrays to store employ information
$SkillsArray = array();
$LevelsArray = array();
$nullFormNums3 = array(); // Array to store null FormNum values
// Fetch and process the results as needed
while ($row = $result->fetch_assoc()) {
    $formNum = $row["FormNum"];
    
    // Process each row of data here
    if ($formNum === null) {
        // Handle case where FormNum is null
    } else {
        // Store education information
  
        $SkillsArray[$formNum] = $row["Skill"];
        $LevelsArray[$formNum] = $row["level"];
    }
}



/* Display stored education information

for ($i = 1; $i <= 7; $i++) {
    if (isset($SkillsArray[$i])) {
        echo "FormNum: $i<br>";
        echo "Skill: " . $SkillsArray[$i] . "<br>";
        echo "Levels: " . $LevelsArray[$i] . "<br>";
        
    } else {
        echo "FormNum $i not found.<br>";
        $nullFormNums3[] = $i;
    }
}

*/

// about me 



$selectQuery = "SELECT * FROM about_me WHERE unique_id = ?";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("s", $Uniqueid);
$stmt->execute();

// Get the results
$result = $stmt->get_result();

// Create arrays to store employ information
$AboutMe = ''; // Array to store null FormNum values
// Fetch and process the results as needed
while ($row = $result->fetch_assoc()) {
 
    // Process each row of data here
    if ($formNum === null) {
        // Handle case where FormNum is null
    } else {
        // Store education information
  
        $AboutMe = $row["description"];
        
    }
}



/* Display stored education information

for ($i = 1; $i <= 7; $i++) {
    if (isset($SkillsArray[$i])) {
        echo "FormNum: $i<br>";
        echo "Skill: " . $SkillsArray[$i] . "<br>";
        echo "Levels: " . $LevelsArray[$i] . "<br>";
        
    } else {
        echo "FormNum $i not found.<br>";
        $nullFormNums3[] = $i;
    }
}
*/



$selectQuery = "SELECT * FROM title WHERE unique_id = ?";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("s", $Uniqueid);
$stmt->execute();

// Get the results
$result = $stmt->get_result();

// Create arrays to store employ information
$UserTitle = ''; // Array to store null FormNum values
// Fetch and process the results as needed
while ($row = $result->fetch_assoc()) {
 
    // Process each row of data here

        // Store education information
  
        $UserTitle = $row["salutation"];
        
        
}
















// echo $UserTitle;
// echo $AboutMe;



// posts ,comments and reply time


$unique_id = $_SESSION['UniqueID']; // Replace with the actual session variables
// not needed in view profile

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// replace this line with viewed_user_id in viewposts.
$user_id = $unique_id; // Replace with the actual user ID

$selectQuery = "SELECT * FROM posts WHERE user_id = ?";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("s", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$posts = array();


// Create an array to store user data
while ($row = $result->fetch_assoc()) {
    $post_id = $row['post_id'];
    $post_image = $row['post_image'];
    $post_desc = $row['post_desc'];
    $post_time = $row['post_time'];

    // Fetch comments for each post
    $commentsQuery = "SELECT * FROM comments WHERE post_id = ?";
    $stmtComments = $conn->prepare($commentsQuery);
    $stmtComments->bind_param("s", $post_id);
    $stmtComments->execute();

    $commentsResult = $stmtComments->get_result();
    $comments = array();

 // Inside your loop that retrieves comments for each post
while ($commentRow = $commentsResult->fetch_assoc()) {
    $comment_id = $commentRow['comm_id']; // Added for reference
   // echo $comment_id;
    $comment = array(
        'comm' => $commentRow["comm"],
        'user_id' => $commentRow["user_id"],
        'comm_time' => $commentRow["comm_time"],
        'comm_id' => $commentRow["comm_id"]
    );

    // Fetch user's first and last name
    $userId = $commentRow["user_id"];
    $firstName = "";
    $lastName = "";
    $personal_comment_image = "";

    // Query entrepreneur table
    $entrepreneurQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image  FROM entrepreneur WHERE unique_id = ?";
    $stmtEntrepreneur = $conn->prepare($entrepreneurQuery);
    $stmtEntrepreneur->bind_param("s", $userId);
    $stmtEntrepreneur->execute();
    $entrepreneurResult = $stmtEntrepreneur->get_result();

    if ($entrepreneurRow = $entrepreneurResult->fetch_assoc()) {
        $firstName = $entrepreneurRow["ent_first_name"];
        $lastName = $entrepreneurRow["ent_given_name"];
        $personal_comment_image =  $entrepreneurRow["entrepreneur_personal_image"];
        if(empty($personal_comment_image)){
            $personal_comment_image = "./Default.jpg";
        }
    } else {
        // Query contributor table
        $contributorQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
        $stmtContributor = $conn->prepare($contributorQuery);
        $stmtContributor->bind_param("s", $userId);
        $stmtContributor->execute();
        $contributorResult = $stmtContributor->get_result();

        if ($contributorRow = $contributorResult->fetch_assoc()) {
            $firstName = $contributorRow["contributor_first_name"];
            $lastName = $contributorRow["contributor_given_name"];
            $personal_comment_image =  $contributorRow["contributor_personal_image"];
            if(empty($personal_comment_image)){
                $personal_comment_image = "./Default.jpg";
            }
        } else {
            // Query buddy table
            $buddyQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
            $stmtBuddy = $conn->prepare($buddyQuery);
            $stmtBuddy->bind_param("s", $userId);
            $stmtBuddy->execute();
            $buddyResult = $stmtBuddy->get_result();

            if ($buddyRow = $buddyResult->fetch_assoc()) {
                $firstName = $buddyRow["bud_first_name"];
                $lastName = $buddyRow["bud_given_name"];
                $personal_comment_image =  $buddyRow["buddy_personal_image"];
                if(empty($personal_comment_image)){
                    $personal_comment_image = "./Default.jpg";
                }
            }
        }
    }


    // here is where we get our replies
    $repliesQuery = "SELECT * FROM reply WHERE comm_id = ?";
    $stmtReplies = $conn->prepare($repliesQuery);
    $stmtReplies->bind_param("s", $comment_id);
    $stmtReplies->execute();

    $repliesResult = $stmtReplies->get_result();
    $replies = array();

    // Inside your loop that retrieves replies for each comment
    while ($replyRow = $repliesResult->fetch_assoc()) {
        $ReplyUserID = $replyRow["user_id"];
        
        $ReplierFname = "";
        $ReplierSname = "";
        $Replier_personal_comment_image = "";

        // Query entrepreneur table
        $entrepreneurQuery = "SELECT ent_first_name, ent_given_name, entrepreneur_personal_image  FROM entrepreneur WHERE unique_id = ?";
        $stmtEntrepreneur = $conn->prepare($entrepreneurQuery);
        $stmtEntrepreneur->bind_param("s", $ReplyUserID);
        $stmtEntrepreneur->execute();
        $entrepreneurResult = $stmtEntrepreneur->get_result();

        if ($entrepreneurRow = $entrepreneurResult->fetch_assoc()) {
            $ReplierFname = $entrepreneurRow["ent_first_name"];
            $ReplierSname = $entrepreneurRow["ent_given_name"];
            $Replier_personal_comment_image =  $entrepreneurRow["entrepreneur_personal_image"];

            if(empty($Replier_personal_comment_image)){
                $Replier_personal_comment_image = "./Default.jpg";
            }
        } else {
            // Query contributor table
            $contributorQuery = "SELECT contributor_first_name, contributor_given_name, contributor_personal_image FROM contributor WHERE unique_id = ?";
            $stmtContributor = $conn->prepare($contributorQuery);
            $stmtContributor->bind_param("s", $ReplyUserID);
            $stmtContributor->execute();
            $contributorResult = $stmtContributor->get_result();

            if ($contributorRow = $contributorResult->fetch_assoc()) {
                $ReplierFname = $contributorRow["contributor_first_name"];
                $ReplierSname= $contributorRow["contributor_given_name"];
                $Replier_personal_comment_image =  $contributorRow["contributor_personal_image"];
                if(empty($Replier_personal_comment_image)){
                    $Replier_personal_comment_image = "./Default.jpg";
                }
            } else {
                // Query buddy table
                $buddyQuery = "SELECT bud_first_name, bud_given_name, buddy_personal_image FROM buddy WHERE unique_id = ?";
                $stmtBuddy = $conn->prepare($buddyQuery);
                $stmtBuddy->bind_param("s", $ReplyUserID);
                $stmtBuddy->execute();
                $buddyResult = $stmtBuddy->get_result();

                if ($buddyRow = $buddyResult->fetch_assoc()) {
                    $ReplierFname = $buddyRow["bud_first_name"];
                    $ReplierSname= $buddyRow["bud_given_name"];
                    $Replier_personal_comment_image =  $buddyRow["buddy_personal_image"];
                    if(empty($Replier_personal_comment_image)){
                        $Replier_personal_comment_image = "./Default.jpg";
                    }
                }
            }
        }

        // Add reply data to the replies array
        $replies[] = array(
            'reply_id' => $replyRow["reply_id"],
            'comment_id' => $commentRow["comm_id"],
            'user_id' => $ReplyUserID,
            'reply' => $replyRow["reply"],
            'first_name' => $ReplierFname,  // Add first name
            'last_name' => $ReplierSname,    // Add last name
            'Personal_image' => $Replier_personal_comment_image // Add image
        );
    }
 
    $comment['first_name'] = $firstName;
    $comment['last_name'] = $lastName;
    $comment['Personal_image'] = $personal_comment_image;
    $comment['replies'] = $replies;
  


    // Add first and last name to the comment




    $comments[] = $comment;
}


    // Store post and its comments in the array
    $posts[] = array(
        'post_id' => $post_id,
        'post_image' => $post_image,
        'post_desc' => $post_desc,
        'post_time' => $post_time,
        'comments' => $comments
    );
}

// Encode the entire array as JSON
$postData = json_encode($posts);
echo "<script>const postData = " . $postData . ";</script>";


/*
$selectQuery = "SELECT * FROM comments WHERE post_id = ?";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("s", $post_id);
$stmt->execute();

// Get the results
$result = $stmt->get_result();

// Create an array to store comments
$comments = array();

// Fetch and process the comments
while ($row = $result->fetch_assoc()) {
    $comments[] = $row["comm"];
}
*/



$conn->close();

// Close the statement
$stmt->close();
$tok = '';
if(isset($_SESSION['ActivationToken'])){
    $tok = $_SESSION['ActivationToken'];
}else{
    $tok = $_SESSION['Logintoken'];
}

$encodedHomeToken = urlencode($tok);
?>
<html lang="en">
<head>
<title> Buddy ChippedIn</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!--<meta http-equiv=""Expires" CONTENT="0">-->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://kit.fontawesome.com/213591b2af.js" 
        crossorigin="anonymous"></script>
        <link rel ="stylesheet" href="css/control_panel/Profile.css">
        <script>

           
     console.log(postData);

        </script> 
</head>
<body>
<div class="container"><!-- NOTE THIS COULD HAVE BEEN "container-fluid"  -->
  <div class="row">
    <div class ="col-sm-12 header">                                                   
      <div class="logo-container">
        <h1 class="TextLogo">SampleText</h1>
      </div>
      <div class="centered-text-container">
        <h2 class="TextLogo">MyProfile</h2>
      </div>
    </div>
  </div>
</div>
<div class="second-header">
<a href="home_page_control_panel2.php?token=<?php echo $encodedHomeToken; ?>" class="header-action-button go-back-button">Go Back</a>

  <div class="Selection">
  <button class="header-action-button View-Posts" > View posts or add a new one</button>
    <button class="header-action-button education-button" > Education</button>
    <button class="header-action-button employment-button"> Employment </button>
    <button class="header-action-button expertise-button"> Expertise </button>
    <button class="header-action-button about-me-button"> About Me</button>
  </div>

</div>
<div class="row-container">
    <div class="circle-and-actions">
        <div class="flex-container">
        <div class="circle-container">
    <?php if ($personal_image_blob): ?>
        <img class="circle-image" id ="user-image" src="<?php echo $personal_image_blob; ?>" alt="User Image">
    <?php else: ?>
        <img class="circle-image" src="./Default.jpg" alt="Default Image">
    <?php endif; ?>
</div>

            <div class="image-actions-container">
                <div class="Names" id= "user-greeting">

                    <?php  if(!empty($UserTitle)){
                    echo "  Hello $UserTitle $first_name $given_name";
                    }else{

                     echo "  Hello  $first_name $given_name";

                    }?>
                </div>
                <form id="upload-form" action="upload.php" method="POST" enctype="multipart/form-data">
    <input type="file" id="image-input" name="image" style="display: none;" accept=".png, .jpeg, .jpg">
    <label for="image-input" class="image-action-button" id="add-image-button">Add Image</label>
        </form>

                <button class="image-action-button" id="delete-image-button">Delete Current Image</button>
                <div class="dropdown">
  <button class="image-action-button" id="add-title-button">Add or Edit Title</button>
  <div class="dropdown-content" id="title-dropdown">
    <a href="#">Mr</a>
    <a href="#">Miss</a>
    <a href="#">DR</a>
    <!-- Add more title options here -->
  </div>
</div>
                
    

             
            </div>
      
        </div>
        <div class = "MemberInfo">
        <br>
            <?php        
         if ($membership_type === "entrepreneur") {
            echo '<span class="grey-text">Business Type:</span> ' . str_replace('_', ' ', $biz_type) . '<br><br>';
            echo '<span class="grey-text">Business Name:</span> ' . str_replace('_', ' ', $biz_name) . '<br><br>';
            echo '<span class="grey-text">Business Category:</span> ' . str_replace('_', ' ', $biz_cat) . '<br><br>';
            echo '<span class="grey-text">Business Description:</span> ' . $biz_desc . '<br><br>';
            echo '<span class="grey-text">Entrepreneur Experience:</span> ' . str_replace('_', ' ', $Ent_experience ) . '<br><br>';
            echo '<span class="grey-text">Expertise Level in above field:</span> ' . str_replace('_', ' ', $Ent_expertise )  . '<br><br>';
            echo '<span class="grey-text">Entrepreneur Highest Qualification:</span> ' . str_replace('_', ' ', $Ent_highest_qual ) . '<br>';
        
    
    
    
            echo "<br><span class='grey-text'>What they want from Buddies:</span><br>";
            foreach ($buddyRetArray as $columnName => $columnValue) {
                echo  str_replace('_', ' ', $columnValue ) . "<br>";
                // this is for testing will be used in html later :) btw, this is disguisting :)
            } 
            
            echo "<br><span class='grey-text'>What they have in place:</span><br>";
            foreach ($entInplaceArray as $columnName => $columnValue) {
                echo  str_replace('_', ' ', $columnValue ) . "<br>";
                // this is for testing will be used in html later :) btw, this is disguisting :)
            }
                
            
        
        
        
        }elseif($membership_type === "buddy"){

            echo '<span class="grey-text">Business Type:</span> ' .str_replace('_', ' ', $bud_business_type ) . '<br><br>';
echo '<span class="grey-text">Buddy Proposal:</span> ' . $bud_proposal . '<br><br>';
echo '<span class="grey-text">Buddy Experience:</span> ' . str_replace('_', ' ',  $bud_experience)  . '<br><br>';
echo '<span class="grey-text">Buddy Expertise:</span> ' . str_replace('_', ' ', $bud_expert) . '<br><br>';
echo '<span class="grey-text">Buddy wants:</span> ' . str_replace('_', ' ', $bud_wants) . '<br>';

echo "<br><span class='grey-text'>What they can offer:</span><br>";
        foreach ($buddyOfferArray as $columnName => $columnValue) {
            echo  str_replace('_', ' ', $columnValue ) . "<br>";
            // this is for testing will be used in html later :) btw, this is disguisting :)
        } 

        
            
        
        echo "<br> <span class='grey-text'> How They would like to assist: </span><br>";
        foreach ($buddyAssistanceArray as $columnName => $columnValue) {
            echo  str_replace('_', ' ', $columnValue ) . "<br>";
            
            // this is for testing will be used in html later :) btw, this is disguisting :)
        } 

        
    }elseif($membership_type === "contributor"){

        echo '<span class="grey-text">Business Type:</span> ' . $cont_type . '<br><br>';
        echo '<span class="grey-text">Business Name:</span> ' . $cont_org_name . '<br><br>';
    
      
       
       
        
    }
            ?>
    
        </br>
        </div>
        </div>



<div class="form-container">
    <div class="form-content">
        <div class= "formEd1">
        <h3>Education Information</h3>
        <form action="process_education.php" method="post" id="education-form">
            
        <label for="school">School/University:</label>

        <input type="text" id="school1" name="school[0]" value="<?php echo isset($schoolsArray[1]) ? $schoolsArray[1] : ''; ?>" required><br><br>

            <label for="degree">Degree/Certificate:</label>
            <input type="text" id="degree1" name="degree[0]" value="<?php echo isset($degreesArray[1]) ? $degreesArray[1] : ''; ?>" required><br><br>

            <label for="major">Major/Field of Study:</label>
            <input type="text" id="major1" name="major[0]" value="<?php echo isset($majorsArray[1]) ? $majorsArray[1] : ''; ?>"  required><br><br>

            <label for="grad_year">Graduation Year:</label>
            <input type="text" id="grad_year1" name="grad_year[0]"  value="<?php echo isset($graduateYearsArray[1]) ? $graduateYearsArray[1] : ''; ?>" required><br><br>
          
          
            <input type="hidden" name="formsdis" id="formsdis-input">
            <input type="hidden" name="FormNum[0]" id="FormNum" value="1">


       
        </div>
        <button  type="button" id="add-education">Edit Second Form</button>
        
        <div class= "formEd2">


            
            <label for="school">School/University:</label>
                <input type="text" id="school2" name="school[1]" value="<?php echo isset($schoolsArray[2]) ? $schoolsArray[2] : ''; ?>" ><br><br>
    
                <label for="degree">Degree/Certificate:</label>
                <input type="text" id="degree2" name="degree[1]"value="<?php echo isset($degreesArray[2]) ? $degreesArray[2] : ''; ?>" ><br><br>
    
                <label for="major">Major/Field of Study:</label>
                <input type="text" id="major2" name="major[1]" value="<?php echo isset($majorsArray[2]) ? $majorsArray[2] : ''; ?>"  ><br><br>
    
                <label for="grad_year">Graduation Year:</label>
                <input type="text" id="grad_year2" name="grad_year[1]"  value="<?php echo isset($graduateYearsArray[2]) ? $graduateYearsArray[2] : ''; ?>"><br><br>
                <input type="hidden" name="FormNum[1]" id="FormNum" value="2">
               
            </div>
            
    
            <button type="button" id="remove-education2">remove Education field</button>

            <button  type="button" id="add-education2">Edit Third form</button>
           
            
            
            
            <div class= "formEd3">


            
            <label for="school">School/University:</label>
                <input type="text" id="school3" name="school[2]" value="<?php echo isset($schoolsArray[3]) ? $schoolsArray[3] : ''; ?>"  ><br><br>
    
                <label for="degree">Degree/Certificate:</label>
                <input type="text" id="degree3" name="degree[2]"value="<?php echo isset($degreesArray[3]) ? $degreesArray[3] : ''; ?>" ><br><br>
    
                <label for="major">Major/Field of Study:</label>
                <input type="text" id="major3" name="major[2]" value="<?php echo isset($majorsArray[3]) ? $majorsArray[3] : ''; ?>" ><br><br>
    
                <label for="grad_year">Graduation Year:</label>
                <input type="text" id="grad_year3" name="grad_year[2]" value="<?php echo isset($graduateYearsArray[3]) ? $graduateYearsArray[3] : ''; ?>" ><br><br>
                <input type="hidden" name="FormNum[2]" id="FormNum" value="3">
               
            </div>
      
            
            <button  type="button" id="remove-education3">remove Education field above</button>


            <input type="submit" value="Submit">
</form>  
    </div>
</div>





<div class="form-container2">
    <div class="form-content2">
        <h3>Employment Information</h3>
        <form action="process_employment.php" method="post" id="employment-form">
            <div class = "formEmp1">
            <label for="Title">Title</label>
            <input type="text" id="title1" name="title[0]" value="<?php echo isset($titleArray[1]) ? $titleArray[1] : ''; ?>"  required><br><br>

            <label for="Employer">Employer:</label>
            <input type="text" id="employer1" name="employer[0]" value="<?php echo isset($employerArray[1]) ? $employerArray[1] : ''; ?>"  required><br><br>

            <label for="Description">Description</label>
            <textarea id="description1" name="description[0]" rows="4" cols="50" required>
            <?php echo isset($descriptionArray[1]) ? $descriptionArray[1] : ''; ?>
            </textarea><br><br>

            <input type="hidden" name="formsdis" id="formsdis-input2">
            <input type="hidden" name="FormNum[0]" id="FormNum" value="1">



            </div>
    
           
        
        
        <button type="button"id="add-employment">Add More</button>


        <div class = "formEmp2">
            <label for="Title">Title</label>
            <input type="text" id="title2" name="title[1]" value="<?php echo isset($titleArray[2]) ? $titleArray[2] : ''; ?>"  ><br><br>

            <label for="Employer">Employer:</label>
            <input type="text" id="employer2" name="employer[1]" value="<?php echo isset($employerArray[2]) ? $employerArray[2] : ''; ?>" ><br><br>

            <label for="Description">Description</label>
            <textarea id="description2" name="description[1]" rows="4" cols="50">
            <?php echo isset($descriptionArray[2]) ? $descriptionArray[2] : ''; ?>
            </textarea><br><br>


     
            <input type="hidden" name="FormNum[1]" id="FormNum" value="2">



            </div>

            
            
            
            
            <button type="button" id="remove-employment2">remove Employment field</button>
            <button type= "button "id="add-employment2">Add More</button>
        


            <div class = "formEmp3">
            <label for="Title">Title</label>
            <input type="text" id="title3" name="title[2]" value="<?php echo isset($titleArray[3]) ? $titleArray[3] : ''; ?>" ><br><br>

            <label for="Employer">Employer:</label>
            <input type="text" id="employer3" name="employer[2]" value="<?php echo isset($employerArray[3]) ? $employerArray[3] : ''; ?>"><br><br>

            <label for="Description">Description</label>
            <textarea id="description3" name="description[2]" rows="4" cols="50">
            <?php echo isset($descriptionArray[3]) ? $descriptionArray[3] : ''; ?>
            </textarea><br><br>


     
            <input type="hidden" name="FormNum[2]" id="FormNum" value="3">



            </div>

                  
            <button type="button" id="remove-employment3">remove Employment field</button>


             <input type="submit" value="Submit">
       
    </form>
    </div>
    </div>




    <div class="form-container3">
    <div class="form-content3">
        <h3>Expertise</h3>
        <form action="process_expertise.php" method="post" id="expertise-form">
            <div class="expertise-fields">
                <div class="expertise-field">
                    <label for="Expertise">Expertise</label>
                    <input type="text" name="Expertise[0]"value="<?php echo isset($SkillsArray[1]) ? $SkillsArray[1] : ''; ?>" required><br><br>
                    <label for="Skill-Level">Skill level form 1-10</label>
                    <input type="number" name="SkillLevel[0]" value="<?php echo isset($LevelsArray[1]) ? $LevelsArray[1] : ''; ?>" required><br><br>
                    <input type="hidden" name="FormNum[0]" value="1">
                </div>
            </div>
            <button type="button" id="add-expertise">Add More</button>
            <input type="hidden" name="formsdis" id="formsdis-input3" value="1">
            <input type="submit" form="expertise-form" value="Submit">
        </form>
    </div>
</div>






<div class="form-container4">
    <div class="form-content4">

        <form action="process_about.php" method="post" id="AboutMe-form">
            <label for="AboutMe">About Me</label>
            <textarea id="AboutMe" name="AboutMe" rows="10" cols="55" required ><?php echo isset($AboutMe) ? $AboutMe : ''; ?></textarea><br><br>


    
           
        
        </form>
       
      
        <input type="submit" form= "AboutMe-form" value="Submit">
    </div>
</div>



<div class="Users-Information">
        <div class="UserInfoContainer">
        
        <h3> Education</h3>

        <?php
    for ($i = 1; $i <= 3; $i++) {
        if (isset($schoolsArray[$i])) {
            
            echo "School: " . $schoolsArray[$i] . "<br>";
            echo "Degree: " . $degreesArray[$i] . "<br>";
            echo "Graduation Year: " . $graduateYearsArray[$i] . "<br>";
            echo "Major: " . $majorsArray[$i] . "<br>";
            echo "<br>";
        } else {
            echo "Education $i is empty.<br>";
        }
    }
    ?>        
        <h3> Employment</h3>
        
        <?php
    for ($i = 1; $i <= 3; $i++) {
        if (isset($employerArray[$i])) {
            
            echo "title: " . $titleArray[$i] . "<br>";
            echo "employer: " . $employerArray[$i] . "<br>";
            echo "description: " . $descriptionArray[$i] . "<br>";
            echo "<br>";
        } else {
            echo "employment $i is empty.<br>";
        }
    }
    ?> 
        
        <h3> Expertise</h3>
       
        <?php
        for ($i = 1; $i <= 7; $i++) {
        if (isset($SkillsArray[$i])) {
            
            echo "Skill: " . $SkillsArray[$i] . "<br>";
            echo "Level: " . $LevelsArray[$i] . "<br>";
            echo "<br>";
        } else {
            echo "Expertise $i is empty.<br>";
        }
         }
         ?> 
        <h3> About Me</h3>
        <br> <?php   if (!empty($AboutMe)){
            echo $AboutMe;
        }else{
            echo "about me is empty";
        } ?></br>
    
        </div>
    </div>

<div class="PostInfo">
<form id="postForm" enctype="multipart/form-data" action="post_upload.php" method="post">
    <div class="AddPostContainer">
        <div class="Image-container">
            <div class="UploadArea" id="uploadArea">
                <p>Drag and drop an image or </p>
            </div>
        </div>
        <div class="UploadControls">
        <input type="file" name="media" id="fileInput" accept="image/*,video/*">
        <p id="selectedFilePath"></p>
    </div>
        <div class="Desc-Container">
            <textarea name="description" placeholder="Write your description here"></textarea>
            <button type="submit" class="PostButton">Post</button>
        </div>
    </div>
    
   
</form>

<div class="View-Posts-Container">
<div class="SavedPostIMG">
    <?php
    foreach ($posts as $post) {
        $post_id = $post['post_id'];
        $post_image = $post['post_image'];
        $post_desc = $post['post_desc'];
        $post_time = $post['post_time'];
    
        echo "<div class='SavedPostContainer' data-postid='$post_id' onclick='openModal(\"$post_id\", postData)'>";
    
        echo "<div class='ImageWrapper'>";
    
        // Check the file extension to determine if it's an image or video
        $fileExtension = strtolower(pathinfo($post_image, PATHINFO_EXTENSION));
        
        if ($fileExtension === 'jpg' || $fileExtension === 'jpeg' || $fileExtension === 'png') {
            // If it's an image, create an image element
            echo "<img src='$post_image' alt='Post Image'>";
        } elseif ($fileExtension === 'mp4' || $fileExtension === 'mov') {
            // If it's a video, create a video element
            echo "<video src='$post_image' controls></video>";
            
        }
    
        // Delete button here for viewing the post
        echo "<form action='delete_post.php' method='post'  onclick='event.stopPropagation();'>";
        echo "<input type='hidden' name='post_id' value='$post_id'>";
        echo "<button type='submit' class='DeleteButton'>Delete</button>";
        echo "</form>";
    
        echo "</div>";
    
        echo "<div class='SavedPostInfo'>";
        echo "<div class='SavedPostDESC'>";
        echo "<p>Description: $post_desc</p>";
        echo "</div>";
        echo "<div class='SavedPostTIME'>";
        echo "<p>Post Time: $post_time</p>";
        echo "</div>";
        echo "</div>";
    
        echo "</div>";
    }
    
    ?>
</div>
</div>
    <!-- The Modal -->
    <div class="modal" id="myModal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>
</div>


    </div>
<script>








document.addEventListener("DOMContentLoaded", function() {
    const uploadForm = document.getElementById("upload-form");

    // Use event delegation to handle change events on file inputs
    document.addEventListener("change", function(event) {
        const target = event.target;
        if (target && target.matches("#image-input")) {
            // Handle the change event for the file input
            console.log("Image Input Changed");
            if (target.files.length > 0) {
                uploadForm.submit();
        }
        }
    });

    // Attach click event listener to the button
    const addImageButton = document.getElementById("add-image-button");
    addImageButton.addEventListener("click", function() {
        console.log("Add Image Button Clicked");
        //document.getElementById("image-input").click();
    });
});


document.addEventListener("DOMContentLoaded", function() {
    const deleteImageButton = document.getElementById("delete-image-button");
    const userImage = document.getElementById("user-image"); 
    
    deleteImageButton.addEventListener("click", function() {
        // Call a function to handle the image deletion
       if(userImage){  // make sure its not null, people like clicking buttons for no reason :)
       deleteProfileImage(userImage);
       };
    });
});

function deleteProfileImage(userImage) {
    // Create an instance of the XMLHttpRequest object
    const xhr = new XMLHttpRequest();

    // Configure the request
    xhr.open("GET", "delete_image.php", true); 
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Display the response from the PHP script
                console.log(xhr.responseText);
                userImage.src = "./Default.jpg";
            } else {
                console.error("Error: " + xhr.status);
            }
        }
    };

    // Send the request
    xhr.send(); 
}













 const buttons = document.querySelectorAll('.header-action-button');

buttons.forEach(button => {
  button.addEventListener('click', () => {
    // Remove the "selected" class from all buttons
    buttons.forEach(btn => btn.classList.remove('selected'));

    // Add the "selected" class to the clicked button
    button.classList.add('selected');
  });
});






  //  Education  Education Education Education Education Education Education Education Education Education Education Education Education Education 



document.addEventListener("DOMContentLoaded", function() {
    const addButton = document.getElementById("add-education");
    const addButton2 = document.getElementById("add-education2");
    const educationButton = document.querySelector(".education-button");
    const formContainer = document.querySelector(".form-container");
    const userInfoContainer = document.querySelector(".UserInfoContainer");
    const removeEducation2Button = document.getElementById("remove-education2");
    const removeEducation3Button = document.getElementById("remove-education3");
    const formEd2 = document.querySelector(".formEd2");
    const formEd3 = document.querySelector(".formEd3");
    let formsdis = 0;
    // Initial state: hide the form container
    formContainer.style.display = "none";
    let isEducationButtonSelected = false;

    addButton.addEventListener("click", function(e) {
        e.preventDefault();

        // Toggle visibility of the second form
        
        formEd2.style.display = "block";
        addButton.style.display = "none"; 
        addButton2.style.display = "flex";
        removeEducation2Button.style.display = "block";
        formsdis++;
        console.log(formsdis);
        updateFormsDisInputValue(formsdis);
    });

    removeEducation2Button.addEventListener("click", function(e) {

        formEd2.style.display = "none";
        addButton.style.display = "flex"; 
        addButton2.style.display = "none";
        removeEducation2Button.style.display = "none";
        formsdis--;
        console.log(formsdis);
        updateFormsDisInputValue(formsdis);
    });



    addButton2.addEventListener("click", function(e) {
        e.preventDefault();
        formEd3.style.display = "block";
        addButton2.style.display = "none";
        removeEducation3Button.style.display = "block";

        formsdis++;
        console.log(formsdis);
        updateFormsDisInputValue(formsdis);

    });


    removeEducation3Button.addEventListener("click", function(e) {

    formEd3.style.display = "none";

    removeEducation3Button.style.display = "none";
    formsdis--;
    updateFormsDisInputValue(formsdis);
    console.log(formsdis);
});



    // Update the event listener for all buttons
    document.querySelectorAll(".header-action-button").forEach(button => {
        button.addEventListener("click", function() {
            if (button === educationButton) {
                isEducationButtonSelected = !isEducationButtonSelected;
                formContainer.style.display = isEducationButtonSelected ? "flex" : "none";
                educationButton.classList.toggle("selected", isEducationButtonSelected);
                userInfoContainer.style.display = isEducationButtonSelected ? "none" : "block";
            } else {
                educationButton.classList.remove("selected");
                formContainer.style.display = "none";
                isEducationButtonSelected = false;  
            }
        });
        
    });

    function updateFormsDisInputValue(value) {
        const formsdisInput = document.getElementById("formsdis-input");
        formsdisInput.value = value;
    } 


// too lazy to change variable names therefore i will just change the values

    document.getElementById("education-form").addEventListener("submit", function(e) {
        // Check if visible fields are filled
        const school2 = document.getElementById("school2").value;
        const degree2 = document.getElementById("degree2").value;
        const major2 = document.getElementById("major2").value;
        const grad_year2 = document.getElementById("grad_year2").value;
        

        // If any of the visible fields are empty, prevent form submission
       
       
       
       
       
       
       
        const school3 = document.getElementById("school3").value;
        const degree3 = document.getElementById("degree3").value;
        const major3 = document.getElementById("major3").value;
        const grad_year3 = document.getElementById("grad_year3").value;
        
       
       
       
       
        if(formsdis === 1){
        if (school2 === "" || degree2 === "" || major2 === "" || grad_year2 === "") {
            e.preventDefault();
            alert("Please fill all visible fields before submitting.");
        }
    }

    

        if(formsdis === 2){
        if (school3 === "" || degree3 === "" || major3 === "" || grad_year3 === "") {
            e.preventDefault();
            alert("Please fill all visible fields before submitting.");
        }
    }
    });






// this works perfectly  and limits amount of educations, pretty good fix if you ask me :)





    
});





// End of Education End of Education End of Education End of Education End of Education End of Education End of Education End of Education End of Education

















//employment employment employment employment  employment employment employment employment employment employment employment employment employment


// employment NOW KEEP IN MIND THE VARIABLE NAMES ARE STILL THE SAME AS EDUCATION, THIS IS BECAUSE I DONT HAVE ALOT OF TIME TO CHANGE VARIABLE NAMES 
// VALUES OF THOSE VARIABLES CORRESPOND WITH EMPLOYMENT NOT EDUCATION , PLEASE KEEP THIS IN MIND



document.addEventListener("DOMContentLoaded", function() {
    const addButton = document.getElementById("add-employment");
    const addButton2 = document.getElementById("add-employment2");
    const educationButton = document.querySelector(".employment-button");
    const formContainer = document.querySelector(".form-container2");
    const userInfoContainer = document.querySelector(".UserInfoContainer");
    const removeEducation2Button = document.getElementById("remove-employment2");
    const removeEducation3Button = document.getElementById("remove-employment3");
    const formEd2 = document.querySelector(".formEmp2");
    const formEd3 = document.querySelector(".formEmp3");
    let formsdis = 0;
    // Initial state: hide the form container
    formContainer.style.display = "none";
    let isEducationButtonSelected = false;

    addButton.addEventListener("click", function(e) {
        e.preventDefault();

        // Toggle visibility of the second form
        
        formEd2.style.display = "block";
        addButton.style.display = "none"; 
        addButton2.style.display = "flex";
        removeEducation2Button.style.display = "block";
        formsdis++;
        console.log(formsdis);
        updateFormsDisInputValue(formsdis);
    });

    removeEducation2Button.addEventListener("click", function(e) {

        formEd2.style.display = "none";
        addButton.style.display = "flex"; 
        addButton2.style.display = "none";
        removeEducation2Button.style.display = "none";
        formsdis--;
        console.log(formsdis);
        updateFormsDisInputValue(formsdis);
    });



    addButton2.addEventListener("click", function(e) {
        e.preventDefault();
        formEd3.style.display = "block";
        addButton2.style.display = "none";
        removeEducation3Button.style.display = "block";

        formsdis++;
        console.log(formsdis);
        updateFormsDisInputValue(formsdis);

    });


    removeEducation3Button.addEventListener("click", function(e) {

    formEd3.style.display = "none";

    removeEducation3Button.style.display = "none";
    formsdis--;
    updateFormsDisInputValue(formsdis);
    console.log(formsdis);
});



    // Update the event listener for all buttons
    document.querySelectorAll(".header-action-button").forEach(button => {
        button.addEventListener("click", function() {
            if (button === educationButton) {
                isEducationButtonSelected = !isEducationButtonSelected;
                formContainer.style.display = isEducationButtonSelected ? "flex" : "none";
                educationButton.classList.toggle("selected", isEducationButtonSelected);
                userInfoContainer.style.display = isEducationButtonSelected ? "none" : "block";
            } else {
                educationButton.classList.remove("selected");
                formContainer.style.display = "none";
                isEducationButtonSelected = false;  
            }
        });
        
    });

    function updateFormsDisInputValue(value) {
        const formsdisInput = document.getElementById("formsdis-input2");
        formsdisInput.value = value;
    } 




    document.getElementById("employment-form").addEventListener("submit", function(e) {
        // Check if visible fields are filled
        const school2 = document.getElementById("title2").value;
        const degree2 = document.getElementById("employer2").value;
        const major2 = document.getElementById("description2").value;
      
        

        // If any of the visible fields are empty, prevent form submission
       
       
       
       
       
       
       
        const school3 = document.getElementById("title3").value;
        const degree3 = document.getElementById("employer3").value;
        const major3 = document.getElementById("description3").value;
 
        
       
       
       
       
        if(formsdis === 1){
        if (school2 === "" || degree2 === "" || major2 === "" ) {
            e.preventDefault();
          
            alert("Please fill all visible fields before submitting.");
        }
    }

    

        if(formsdis === 2){
        if (school3 === "" || degree3 === "" || major3 === "" ) {
            e.preventDefault();
            alert("Please fill all visible fields before submitting.");
        }
    }
    });






// this works perfectly  and limits amount of educations, pretty good fix if you ask me :)





    
});

//END employment END employment END employment END employment END employment END employment END employment END employment END employment END employment

















document.addEventListener("DOMContentLoaded", function() {

    const expertiseButton = document.querySelector(".expertise-button");
    const formContainer = document.querySelector(".form-container3");
    const userInfoContainer = document.querySelector(".UserInfoContainer");
    // Initial state: hide the form container
    formContainer.style.display = "none";
    let isExpertiseButtonSelected = false;

    



  let formsdis = 0;


  const addButton = document.getElementById("add-expertise");
    const expertiseFields = document.querySelector(".expertise-fields");
    // Initialize with the existing field

    addButton.addEventListener("click", function(e) {
        e.preventDefault();

        if (formsdis < 7) {
            formsdis++;
            updateFormsDisInputValue(formsdis);
            const newField = createExpertiseField(formsdis);
            expertiseFields.appendChild(newField);
            
            if (formsdis === 7) {
                addButton.style.display = "none";
            }
        }
    });

    function updateFormsDisInputValue(value) {
        const formsdisInput = document.getElementById("formsdis-input3");
        formsdisInput.value = value;
        console.log(  formsdisInput.value);
    }
    

    const expertiseData = <?php echo json_encode($SkillsArray); ?>;
    const levelsData = <?php echo json_encode($LevelsArray); ?>;
    
    console.log("expertiseData:", expertiseData);
    console.log("levelsData:", levelsData);

    function createExpertiseField(index) {
    const fieldDiv = document.createElement("div");
    fieldDiv.className = "expertise-field";

    const expertiseValue = expertiseData[index+1] || ""; // Default to empty string if not available
    const skillLevelValue = levelsData[index+1] || "";   // Default to empty string if not available 

    fieldDiv.innerHTML = `
        <label for="Expertise">Expertise</label>
        <input type="text" name="Expertise[]" value="${expertiseValue}" required><br><br>
        <label for="Skill-Level">Skill level form 1-10</label>
        <input type="number" name="SkillLevel[]" value="${skillLevelValue}" required><br><br>
        <input type="hidden" name="FormNum[]" value="${index + 1}">
    `;

    const removeButton = document.createElement("button");
    removeButton.type = "button";
    removeButton.textContent = "Remove";
    removeButton.addEventListener("click", function() {
        expertiseFields.removeChild(fieldDiv);
        formsdis--;
        updateFormsDisInputValue(formsdis);
        addButton.style.display = "block";
    });

    fieldDiv.appendChild(removeButton);
    return fieldDiv;
}



    // Update the event listener for all buttons
    document.querySelectorAll(".header-action-button").forEach(button => {
        button.addEventListener("click", function() {
            if (button === expertiseButton) {
                isExpertiseButtonSelected = !isExpertiseButtonSelected;
                formContainer.style.display = isExpertiseButtonSelected ? "flex" : "none";
                expertiseButton.classList.toggle("selected", isExpertiseButtonSelected);
                userInfoContainer.style.display = isExpertiseButtonSelected ? "none" : "block";
            } else {
                expertiseButton.classList.remove("selected");
                formContainer.style.display = "none";
                isExpertiseButtonSelected = false;
            }
        });
    });



    
});



document.addEventListener("DOMContentLoaded", function() {
    const formContainer = document.querySelector(".form-container4");
    const aboutMeButton = document.querySelector(".about-me-button");
    const userInfoContainer = document.querySelector(".UserInfoContainer");
    // Initial state: hide the form container
    formContainer.style.display = "none";
    let isAboutMeButtonSelected = false;

    // Update the event listener for the about me button




    document.querySelectorAll(".header-action-button").forEach(button => {
        button.addEventListener("click", function() {
            if (button === aboutMeButton) {
                isAboutMeButtonSelected = !isAboutMeButtonSelected;
                formContainer.style.display = isAboutMeButtonSelected ? "flex" : "none";
                aboutMeButton.classList.toggle("selected", isAboutMeButtonSelected);
                userInfoContainer.style.display = isAboutMeButtonSelected ? "none" : "block";
            } else {
                aboutMeButton.classList.remove("selected");
                formContainer.style.display = "none";
                isAboutMeButtonSelected = false;
            }
        });
    });


    document.querySelector("#AboutMe-form").addEventListener("submit", function(e) {
        const forms = document.querySelectorAll("#AboutMe-form");
        let allFilled = true;

        forms.forEach(form => {
            const textarea = form.querySelector("textarea");

            if (textarea.value.trim() === "") {
                allFilled = false;
                textarea.style.border = "2px solid red";
            }
        });

        if (!allFilled) {
            e.preventDefault();
            alert("Please fill in all the fields.");
        }
    });
});






// View and add post tiem
document.addEventListener("DOMContentLoaded", function() {
    const formContainer = document.querySelector(".PostInfo");
    const aboutMeButton = document.querySelector(".View-Posts");
    const userInfoContainer = document.querySelector(".UserInfoContainer");
    // Initial state: hide the form container
    formContainer.style.display = "none";
    let isAboutMeButtonSelected = false;

    // Update the event listener for the about me button




    document.querySelectorAll(".header-action-button").forEach(button => {
        button.addEventListener("click", function() {
            if (button === aboutMeButton) {
                isAboutMeButtonSelected = !isAboutMeButtonSelected;
                formContainer.style.display = isAboutMeButtonSelected ? "block" : "none";
                aboutMeButton.classList.toggle("selected", isAboutMeButtonSelected);
                userInfoContainer.style.display = isAboutMeButtonSelected ? "none" : "block";
            } else {
                aboutMeButton.classList.remove("selected");
                formContainer.style.display = "none";
                isAboutMeButtonSelected = false;
            }
        });
    });


});














document.addEventListener("DOMContentLoaded", function() {
    const addButton = document.getElementById("add-title-button");
    const dropdown = document.getElementById("title-dropdown");

    addButton.addEventListener("click", function() {
      if (dropdown.style.display === "none") {
        dropdown.style.display = "block";
      } else {
        dropdown.style.display = "none";
      }
    });

    const titleLinks = dropdown.getElementsByTagName("a");
    for (const link of titleLinks) {
      link.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent the default link behavior
        const chosenTitle = link.textContent; // Get the chosen title
        sendDataToServer(chosenTitle);
      });
    }

    function sendDataToServer(title) {
      const url = "process_title.php";
      const headers = {
        "Content-Type": "application/x-www-form-urlencoded"
      };
      const body = "title=" + encodeURIComponent(title);

      fetch(url, {
        method: "POST",
        headers: headers,
        body: body
      })
      .then(response => {
        if (response.ok) {
          console.log("Title sent successfully!");
          window.location.reload();
        } else {
          console.log("Failed to send title.");
        }
      })
      .catch(error => {
        console.error("Error:", error);
      });
    }

});







// post time

const uploadArea = document.getElementById('uploadArea');
  const fileInput = document.getElementById('fileInput');
  const selectedFilePath = document.getElementById('selectedFilePath');
  const imageContainer = document.querySelector('.Image-container');

  uploadArea.addEventListener('dragover', (event) => {
      event.preventDefault();
      uploadArea.style.border = '2px dashed #007bff';
  });

  uploadArea.addEventListener('dragleave', () => {
      uploadArea.style.border = '2px dashed #ccc';
  });

  uploadArea.addEventListener('drop', (event) => {
      event.preventDefault();
      uploadArea.style.border = '2px dashed #ccc';

      const file = event.dataTransfer.files[0];
      handleFileUpload(file);
      fileInput.files = event.dataTransfer.files; // Update the fileInput's files property
  });

  fileInput.addEventListener('change', (event) => {
      const file = event.target.files[0];
      handleFileUpload(file);
  });

  function handleFileUpload(file) {
      if (file) {
          // Update the selected file path display
          // selectedFilePath.textContent = `Selected file: ${file.name}`;
          uploadArea.textContent = '';
          
          const reader = new FileReader();

          if (file.type.startsWith('image/')) {
              // Display the uploaded image in the UploadArea
              reader.onload = function(event) {
                  uploadArea.style.backgroundImage = `url('${event.target.result}')`;
                  uploadArea.style.backgroundSize = '100% auto'; // Adjust the background size to fill width and adjust height
                  uploadArea.style.backgroundRepeat = 'no-repeat'; // Prevent repeating the background
                  uploadArea.style.backgroundPosition = 'center'; // Center the background
              };
          } else if (file.type.startsWith('video/')) {
              // Display the uploaded video using a video element
              const video = document.createElement('video');
              video.src = URL.createObjectURL(file);
              video.controls = true;

              // Set the dimensions to match the upload area
              video.style.width = '100%';
              video.style.height = '100%';

              uploadArea.appendChild(video);
          }

          reader.readAsDataURL(file);

          // Process the uploaded file here
          console.log('Uploaded file:', file);
      }
  }


/* not gonna lie, im starting to go a bit insane :)
const postForm = document.getElementById('postForm');

postForm.addEventListener('submit', (event) => {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(postForm);

    fetch('post_upload.php', {  
        method: 'POST',
        body: formData
    })
    .catch(error => {
        console.error('Error:', error);
    });
});



MODAL TIME*/
const modal = document.getElementById('myModal');
const modalContent = document.getElementById('modalContent');
const closeModal = document.getElementById('closeModal');

const openCommentSections = {}; // To track open comment sections

function openModal(postId, postData) {
    console.log(postData);
    const clickedPost = document.querySelector(`.SavedPostContainer[data-postid="${postId}"]`);
    let postImage;
const imgElement = clickedPost.querySelector('img');
const videoElement = clickedPost.querySelector('video');

if (imgElement) {
    // If an img element is found, get its src
    postImage = imgElement.src;

    
} else if (videoElement) {
    // If a video element is found, get its src
    postImage = videoElement.src;
}


    const postDescription = clickedPost.querySelector('.SavedPostDESC').innerHTML;
    const postTime = clickedPost.querySelector('.SavedPostTIME').innerHTML;

    const commentsSectionId = `commentsSection${postId}`;
    const isCommentSectionOpen = openCommentSections[commentsSectionId];

    let commentsSectionContent = '';
    if (isCommentSectionOpen) {
        commentsSectionContent = '';
        openCommentSections[commentsSectionId] = false;
    } else {
        commentsSectionContent = `
    <form class="comment-form">
        <textarea class='comment-textarea' rows='2'  placeholder='Type your comment here'></textarea>
        <button type="button" class="post-comment-button">Post Comment</button>
    </form>
    <div class="comments-list">
        <!-- Comments will be displayed here -->
        ${postData
            .filter(post => post.post_id === postId)
            .map(post => post.comments.map(comment => `
    <div class="comment">
        <div class="comment-author">
            <img src="${comment.Personal_image}" alt="User Image">
            <p>${comment.first_name} ${comment.last_name} commented:</p>
        </div>
        <p>${comment.comm}</p>
        <button class="reply-button">View Replies</button>
        <div class="reply-area" style="display: none;">
        <form id="reply-form">
        <textarea class='comment-textarea' name="reply" rows='2' placeholder='Type your reply here'></textarea>
        <button type="button" class="post-comment-button reply-post-button">Post Reply</button>
        <input type="hidden" class="comment-id" name="commentId" value="${comment.comm_id}">
        <input type="hidden" class="post-id" name="postId" value="${post.post_id}">
        </form>
        </div>

        <div class="replies" style="display: none;">
        ${comment.replies.map(reply => `
        <div class="reply">
            <div class="reply-author">
                <img src="${reply.Personal_image}" alt="User Image">
                <p>${reply.first_name} ${reply.last_name} replied:</p>
            </div>
            <p>${reply.reply}</p>
        </div>
    `).join('')}
        </div>
    </div>
`).join(''))

        }
    </div>
`;


    openCommentSections[commentsSectionId] = true;
}




  // Determine if the postImage is an image or a video based on its file extension
var fileExtension = postImage.split('.').pop().toLowerCase();
var isVideo = fileExtension === 'mp4' || fileExtension === 'mov';

modalContent.innerHTML = `
    <div class='modal-media'>
        ${isVideo ? `<video src='${postImage}' controls class='modal-video'></video>` : `<img src='${postImage}' alt='Post Image' class='modal-image'>`}
    </div>
    <div class='modal-info'>
        <div class='modal-description'>
            ${postDescription}
        </div>
        <div class='modal-time'>    
            ${postTime}
        </div>
        <button class='view-comments-button' onclick='toggleCommentSection("${commentsSectionId}")'>
            ${isCommentSectionOpen ? 'Hide Comments' : 'View Comments'}
        </button>
    </div>
    <div class='comments-section' id='${commentsSectionId}'>
        ${commentsSectionContent}
    </div>
`;

const commentsSection = document.getElementById(commentsSectionId);
const postCommentButton = commentsSection.querySelector('.post-comment-button');
    postCommentButton.addEventListener('click', () => {
        const commentTextarea = commentsSection.querySelector('.comment-textarea');
        const commentText = commentTextarea.value.trim();
        if (commentText !== '') {
            postComment(postId, commentText);
            // Update the comments section with the newly posted comment
            commentsSection.insertAdjacentHTML('beforeend', `<p>${commentText}</p>`);
            commentTextarea.value = ''; // Clear the textarea
        }
    });
    modal.style.display = 'block';


   const replyButtons = document.querySelectorAll('.reply-button');

    replyButtons.forEach(button => {
        button.addEventListener('click', () => {
            const replyArea = button.nextElementSibling;
            const replyPostButton = replyArea.querySelector('.reply-post-button');
            const replies = replyArea.nextElementSibling;

            // Toggle the reply area's display
            if (replyArea.style.display === 'none' || replyArea.style.display === '') {
                replyArea.style.display = 'block'; // Show the reply area
                replyPostButton.style.display = 'block'; // Show the reply post button
                replies.style.display = 'block'; // Show the replies section
            } else {
                replyArea.style.display = 'none'; // Hide the reply area
                replyPostButton.style.display = 'none'; // Hide the reply post button
                replies.style.display = 'none'; // Hide the replies section
            }
        });
    });







// Add an event listener to all "Post Reply" buttons
const replyPostButtons = document.querySelectorAll('.reply-post-button');
replyPostButtons.forEach(button => {
    button.addEventListener('click', () => {
        const form = button.closest('form#reply-form');

        // Get the reply textarea within the form
        const replyTextarea = form.querySelector('.comment-textarea');
        const replyText = replyTextarea.value.trim(); // Get the trimmed value

        // Check if the reply text is empty
        if (replyText === '') {
            // Display an error message or take any other action you prefer
            console.error('Reply text is empty. Please enter a reply.');
            return; // Prevent form submission
        }

        // Prevent the default form submission
        event.preventDefault();

        // Get the form data
        const formData = new FormData(form);

        // Send a POST request to reply_post.php
        fetch('reply_post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                // The reply was successfully posted, you can handle this case here
                // You may want to refresh the comments or take other actions
                form.reset(); // Clear the form
                window.location.reload();

            } else {
                // Handle the case where the POST request fails
                console.error('Failed to post reply');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});

/*
const viewRepliesButton = document.querySelector('.reply-button');
// Initialize a flag to track the state
let repliesVisible = false;

// Function to toggle the text of the button
 function toggleButtonText() {
    repliesVisible = !repliesVisible; // Toggle the state

    // Change the button text based on the state
    if (repliesVisible) {
        viewRepliesButton.textContent = 'Hide Replies';
    } else {
        viewRepliesButton.textContent = 'View Replies';
    }

    // You can also toggle the visibility of the replies here
    // For example, if the replies are in a div with class "replies"
    const repliesDiv = document.querySelector('.replies');
    repliesDiv.style.display = repliesVisible ? 'block' : 'none';   
}

// Add a click event listener to the button
viewRepliesButton.addEventListener('click', toggleButtonText);
*/


replyButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Find the closest comment container for this button
        const container = button.closest('.comment');

        // Find the corresponding replies div, reply text area, and post reply button within the container
        const repliesDiv = container.querySelector('.replies');
        const replyTextArea = container.querySelector('[name="reply"]'); // Select by name
        const postReplyButton = container.querySelector('.reply-post-button');
        const replyArea = container.querySelector('.reply-area');

        // Toggle the visibility of the replies
        if (repliesDiv.classList.contains('reply-hidden')) {
            repliesDiv.classList.remove('reply-hidden');
            repliesDiv.classList.add('reply-visible');
            button.textContent = 'Hide Replies';
        } else {
            repliesDiv.classList.remove('reply-visible');
            repliesDiv.classList.add('reply-hidden');
            button.textContent = 'View Replies';
        }

        // Toggle the visibility of the reply text area and post reply button
        if (replyArea.classList.contains('reply-hidden')) {
            replyArea.classList.remove('reply-hidden');
            replyArea.classList.add('reply-visible');
            replyTextArea.classList.remove('reply-hidden');
            replyTextArea.classList.add('reply-visible');
            postReplyButton.classList.remove('reply-hidden');
            postReplyButton.classList.add('reply-visible');
        } else {
            replyArea.classList.remove('reply-visible');
            replyArea.classList.add('reply-hidden');
            replyTextArea.classList.remove('reply-visible');
            replyTextArea.classList.add('reply-hidden');
            postReplyButton.classList.remove('reply-visible');
            postReplyButton.classList.add('reply-hidden');
        }
    });
});


}

// Add an event listener to all "Reply" buttons

// Rest of your code...

function toggleCommentSection(commentsSectionId) {
    const commentsSection = document.getElementById(commentsSectionId);
    const isCommentSectionOpen = openCommentSections[commentsSectionId];

    if (isCommentSectionOpen) {
        commentsSection.style.display = 'none'; // Hide the comment section
    } else {
        commentsSection.style.display = 'block'; // Show the comment section
    }

    openCommentSections[commentsSectionId] = !isCommentSectionOpen;

    const button = commentsSection.previousElementSibling.querySelector('.view-comments-button');
    button.textContent = isCommentSectionOpen ? 'View Comments' : 'Hide Comments';
}


function closeModalFunction() {
    modal.style.display = 'none';
    modalContent.innerHTML = ''; // Clear modal content

    // Reset the button text and comment section state
    for (const sectionId in openCommentSections) {
        const button = document.querySelector(`#${sectionId} ~ .modal-info .view-comments-button`);
        if (button) {
            button.textContent = 'View Comments';
        }
        openCommentSections[sectionId] = false;
    }
}

document.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeModalFunction();
    }
});

closeModal.addEventListener('click', closeModalFunction);



function openCommentSection(postId) {
    const commentsSection = document.getElementById(`commentsSection${postId}`);
    commentsSection.innerHTML = `
        <form class="comment-form">
            <textarea class='comment-textarea' placeholder='Type your comment here'></textarea>
            <button type="button" class="post-comment-button">Post Comment</button>
        </form>
    `;
    
   
}

function postComment(postId, commentText) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'post_comment.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Comment posted successfully
                window.location.reload();

            } else {
                // Handle error if needed
            }
        }
    };
    xhr.send(`post_id=${postId}&comment=${encodeURIComponent(commentText)}`);
}


</script>
</body>
</html>