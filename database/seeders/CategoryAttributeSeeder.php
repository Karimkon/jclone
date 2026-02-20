<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoryAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            151 => [ // Vehicles
                ['key' => 'year', 'label' => 'Year', 'type' => 'number', 'placeholder' => 'e.g. 2022', 'required' => false, 'options' => null],
                ['key' => 'make', 'label' => 'Make', 'type' => 'text', 'placeholder' => 'e.g. Toyota', 'required' => true, 'options' => null],
                ['key' => 'model', 'label' => 'Model', 'type' => 'text', 'placeholder' => 'e.g. Land Cruiser', 'required' => true, 'options' => null],
                ['key' => 'engine_code', 'label' => 'Engine Code', 'type' => 'text', 'placeholder' => 'e.g. 1GD-FTV', 'required' => false, 'options' => null],
                ['key' => 'engine_size', 'label' => 'Engine Size', 'type' => 'text', 'placeholder' => 'e.g. 2,362 cc', 'required' => false, 'options' => null],
                ['key' => 'fuel_type', 'label' => 'Fuel Type', 'type' => 'select', 'placeholder' => null, 'required' => true, 'options' => ['Petrol', 'Diesel', 'Electric', 'Hybrid']],
                ['key' => 'transmission', 'label' => 'Transmission', 'type' => 'select', 'placeholder' => null, 'required' => true, 'options' => ['Automatic', 'Manual', 'CVT', 'Semi-Automatic']],
                ['key' => 'drivetrain', 'label' => 'Drivetrain', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['2WD', '4WD', 'AWD', 'FWD', 'RWD']],
                ['key' => 'body_type', 'label' => 'Body Type', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Sedan', 'SUV', 'Hatchback', 'Pickup', 'Van', 'Coupe', 'Wagon', 'Bus', 'Truck']],
                ['key' => 'mileage', 'label' => 'Mileage (km)', 'type' => 'number', 'placeholder' => 'e.g. 85000', 'required' => false, 'options' => null],
                ['key' => 'doors', 'label' => 'Doors', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['2', '3', '4', '5']],
                ['key' => 'seats', 'label' => 'Seats', 'type' => 'number', 'placeholder' => 'e.g. 5', 'required' => false, 'options' => null],
                ['key' => 'color', 'label' => 'Color', 'type' => 'text', 'placeholder' => 'e.g. White', 'required' => false, 'options' => null],
                ['key' => 'registration_status', 'label' => 'Registration', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Registered', 'Unregistered', 'Foreign Used']],
            ],
            2 => [ // Mobile Phones & Tablets
                ['key' => 'brand', 'label' => 'Brand', 'type' => 'text', 'placeholder' => 'e.g. Samsung', 'required' => true, 'options' => null],
                ['key' => 'model', 'label' => 'Model', 'type' => 'text', 'placeholder' => 'e.g. Galaxy S24 Ultra', 'required' => true, 'options' => null],
                ['key' => 'storage', 'label' => 'Storage', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['16GB', '32GB', '64GB', '128GB', '256GB', '512GB', '1TB']],
                ['key' => 'ram', 'label' => 'RAM', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['2GB', '3GB', '4GB', '6GB', '8GB', '12GB', '16GB']],
                ['key' => 'screen_size', 'label' => 'Screen Size', 'type' => 'text', 'placeholder' => 'e.g. 6.8 inches', 'required' => false, 'options' => null],
                ['key' => 'battery', 'label' => 'Battery', 'type' => 'text', 'placeholder' => 'e.g. 5000 mAh', 'required' => false, 'options' => null],
                ['key' => 'os', 'label' => 'Operating System', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Android', 'iOS', 'HarmonyOS', 'Other']],
                ['key' => 'processor', 'label' => 'Processor', 'type' => 'text', 'placeholder' => 'e.g. Snapdragon 8 Gen 3', 'required' => false, 'options' => null],
                ['key' => 'camera', 'label' => 'Camera', 'type' => 'text', 'placeholder' => 'e.g. 200MP + 12MP + 10MP', 'required' => false, 'options' => null],
            ],
            5 => [ // Clothing & Apparel
                ['key' => 'brand', 'label' => 'Brand', 'type' => 'text', 'placeholder' => 'e.g. Nike', 'required' => false, 'options' => null],
                ['key' => 'material', 'label' => 'Material', 'type' => 'text', 'placeholder' => 'e.g. 100% Cotton', 'required' => false, 'options' => null],
                ['key' => 'fit', 'label' => 'Fit', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Regular', 'Slim', 'Loose', 'Oversized']],
                ['key' => 'gender', 'label' => 'Gender', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Men', 'Women', 'Unisex', 'Boys', 'Girls']],
                ['key' => 'care_instructions', 'label' => 'Care Instructions', 'type' => 'text', 'placeholder' => 'e.g. Machine wash cold', 'required' => false, 'options' => null],
            ],
            16 => [ // Electronics & Tech
                ['key' => 'brand', 'label' => 'Brand', 'type' => 'text', 'placeholder' => 'e.g. Sony', 'required' => false, 'options' => null],
                ['key' => 'model', 'label' => 'Model', 'type' => 'text', 'placeholder' => 'e.g. WH-1000XM5', 'required' => false, 'options' => null],
                ['key' => 'warranty', 'label' => 'Warranty', 'type' => 'text', 'placeholder' => 'e.g. 1 Year', 'required' => false, 'options' => null],
                ['key' => 'power_rating', 'label' => 'Power Rating', 'type' => 'text', 'placeholder' => 'e.g. 100W', 'required' => false, 'options' => null],
                ['key' => 'connectivity', 'label' => 'Connectivity', 'type' => 'text', 'placeholder' => 'e.g. Bluetooth 5.3, WiFi', 'required' => false, 'options' => null],
            ],
            17 => [ // Computer & Accessories
                ['key' => 'brand', 'label' => 'Brand', 'type' => 'text', 'placeholder' => 'e.g. Dell', 'required' => true, 'options' => null],
                ['key' => 'processor', 'label' => 'Processor', 'type' => 'text', 'placeholder' => 'e.g. Intel Core i7-13700H', 'required' => false, 'options' => null],
                ['key' => 'ram', 'label' => 'RAM', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['4GB', '8GB', '16GB', '32GB', '64GB']],
                ['key' => 'storage', 'label' => 'Storage', 'type' => 'text', 'placeholder' => 'e.g. 512GB SSD', 'required' => false, 'options' => null],
                ['key' => 'screen_size', 'label' => 'Screen Size', 'type' => 'text', 'placeholder' => 'e.g. 15.6 inches', 'required' => false, 'options' => null],
                ['key' => 'graphics_card', 'label' => 'Graphics Card', 'type' => 'text', 'placeholder' => 'e.g. NVIDIA RTX 4060', 'required' => false, 'options' => null],
                ['key' => 'os', 'label' => 'Operating System', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Windows 11', 'Windows 10', 'macOS', 'Linux', 'ChromeOS', 'None']],
            ],
            219 => [ // Real Estate & Property
                ['key' => 'property_type', 'label' => 'Property Type', 'type' => 'select', 'placeholder' => null, 'required' => true, 'options' => ['Apartment', 'House', 'Land', 'Commercial', 'Office', 'Warehouse']],
                ['key' => 'bedrooms', 'label' => 'Bedrooms', 'type' => 'number', 'placeholder' => 'e.g. 3', 'required' => false, 'options' => null],
                ['key' => 'bathrooms', 'label' => 'Bathrooms', 'type' => 'number', 'placeholder' => 'e.g. 2', 'required' => false, 'options' => null],
                ['key' => 'area_sqm', 'label' => 'Area (sqm)', 'type' => 'number', 'placeholder' => 'e.g. 120', 'required' => false, 'options' => null],
                ['key' => 'parking_spaces', 'label' => 'Parking Spaces', 'type' => 'number', 'placeholder' => 'e.g. 2', 'required' => false, 'options' => null],
                ['key' => 'furnished', 'label' => 'Furnished', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Furnished', 'Semi-Furnished', 'Unfurnished']],
            ],
            11 => [ // Home & Appliances
                ['key' => 'brand', 'label' => 'Brand', 'type' => 'text', 'placeholder' => 'e.g. LG', 'required' => false, 'options' => null],
                ['key' => 'model', 'label' => 'Model', 'type' => 'text', 'placeholder' => 'e.g. F4V5VYP2T', 'required' => false, 'options' => null],
                ['key' => 'power_rating', 'label' => 'Power Rating', 'type' => 'text', 'placeholder' => 'e.g. 2000W', 'required' => false, 'options' => null],
                ['key' => 'voltage', 'label' => 'Voltage', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['110V', '220V', '110-240V']],
                ['key' => 'warranty', 'label' => 'Warranty', 'type' => 'text', 'placeholder' => 'e.g. 2 Years', 'required' => false, 'options' => null],
            ],
            90 => [ // Furniture & Accessories
                ['key' => 'material', 'label' => 'Material', 'type' => 'text', 'placeholder' => 'e.g. Solid Wood', 'required' => false, 'options' => null],
                ['key' => 'dimensions', 'label' => 'Dimensions', 'type' => 'text', 'placeholder' => 'e.g. 120x60x75 cm', 'required' => false, 'options' => null],
                ['key' => 'color', 'label' => 'Color', 'type' => 'text', 'placeholder' => 'e.g. Walnut Brown', 'required' => false, 'options' => null],
                ['key' => 'assembly_required', 'label' => 'Assembly Required', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Yes', 'No']],
            ],
            233 => [ // Agriculture & Farming
                ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'placeholder' => 'e.g. Tractor, Seeds, Fertilizer', 'required' => false, 'options' => null],
                ['key' => 'weight', 'label' => 'Weight', 'type' => 'text', 'placeholder' => 'e.g. 50 kg', 'required' => false, 'options' => null],
                ['key' => 'capacity', 'label' => 'Capacity', 'type' => 'text', 'placeholder' => 'e.g. 500 litres', 'required' => false, 'options' => null],
                ['key' => 'power_source', 'label' => 'Power Source', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Manual', 'Electric', 'Diesel', 'Petrol', 'Solar']],
            ],
            19 => [ // Sports & Health
                ['key' => 'brand', 'label' => 'Brand', 'type' => 'text', 'placeholder' => 'e.g. Adidas', 'required' => false, 'options' => null],
                ['key' => 'size', 'label' => 'Size', 'type' => 'text', 'placeholder' => 'e.g. Medium, 42', 'required' => false, 'options' => null],
                ['key' => 'material', 'label' => 'Material', 'type' => 'text', 'placeholder' => 'e.g. Polyester', 'required' => false, 'options' => null],
                ['key' => 'gender', 'label' => 'Gender', 'type' => 'select', 'placeholder' => null, 'required' => false, 'options' => ['Men', 'Women', 'Unisex']],
                ['key' => 'sport_type', 'label' => 'Sport Type', 'type' => 'text', 'placeholder' => 'e.g. Football, Gym, Running', 'required' => false, 'options' => null],
            ],
        ];

        foreach ($definitions as $categoryId => $fields) {
            $category = Category::find($categoryId);
            if (!$category) {
                $this->command->warn("Category ID {$categoryId} not found, skipping.");
                continue;
            }

            // Merge with existing meta to preserve other data
            $meta = $category->meta ?? [];
            $meta['attribute_fields'] = $fields;
            $category->meta = $meta;
            $category->save();

            $this->command->info("Seeded {$category->name} with " . count($fields) . " attribute fields.");
        }

        $this->command->info('Category attribute seeding complete!');
    }
}
