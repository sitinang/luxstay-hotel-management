<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\RoomType;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deluxe = RoomType::create([
            'name' => 'Deluxe Room',
            'description' => 'A spacious room with a king-size bed, private balcony, and high-end amenities.',
            'price_per_night' => 2250000,
            'capacity' => 2,
            'amenities' => ['Wi-Fi', 'King Bed', 'Balcony', 'Minibar', 'TV'],
            'image' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?q=80&w=1974&auto=format&fit=crop',
        ]);

        $suite = RoomType::create([
            'name' => 'Executive Suite',
            'description' => 'The ultimate luxury experience with a separate living area and panoramic views.',
            'price_per_night' => 5250000,
            'capacity' => 4,
            'amenities' => ['Wi-Fi', '2 King Beds', 'Living Area', 'Mini Kitchen', 'Panoramic View'],
            'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=2070&auto=format&fit=crop',
        ]);

        $standard = RoomType::create([
            'name' => 'Standard Room',
            'description' => 'A cozy room perfect for short stays with all basic necessities.',
            'price_per_night' => 1275000,
            'capacity' => 2,
            'amenities' => ['Wi-Fi', 'Queen Bed', 'Work Desk', 'TV'],
            'image' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?q=80&w=2070&auto=format&fit=crop',
        ]);

        $presidential = RoomType::create([
            'name' => 'Presidential Suite',
            'description' => 'Experience unmatched grandeur in our most prestigious suite, featuring a private pool and personal butler service.',
            'price_per_night' => 11250000,
            'capacity' => 2,
            'amenities' => ['Wi-Fi', 'Super King Bed', 'Private Pool', 'Butler Service', 'Home Theater'],
            'image' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=2070&auto=format&fit=crop',
        ]);

        $junior = RoomType::create([
            'name' => 'Junior Suite',
            'description' => 'A perfect blend of luxury and comfort, ideal for travelers who appreciate extra space and style.',
            'price_per_night' => 3300000,
            'capacity' => 2,
            'amenities' => ['Wi-Fi', 'King Bed', 'Sitting Area', 'Espresso Machine', 'TV'],
            'image' => 'https://images.unsplash.com/photo-1590490359683-658d3d23f972?q=80&w=1974&auto=format&fit=crop',
        ]);

        $family = RoomType::create([
            'name' => 'Family Room',
            'description' => 'Designed with families in mind, this room offers multiple beds and plenty of space for everyone to relax.',
            'price_per_night' => 4200000,
            'capacity' => 5,
            'amenities' => ['Wi-Fi', '2 Queen Beds', '1 Single Bed', 'Play Area', 'Kitchenette'],
            'image' => 'https://images.unsplash.com/photo-1566195992011-5f6b21e539aa?q=80&w=1974&auto=format&fit=crop',
        ]);

        $superior = RoomType::create([
            'name' => 'Superior Room',
            'description' => 'Enhanced comfort with modern decor and premium bedding for a restful stay.',
            'price_per_night' => 1800000,
            'capacity' => 2,
            'amenities' => ['Wi-Fi', 'Double Bed', 'City View', 'Work Station', 'TV'],
            'image' => 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?q=80&w=2070&auto=format&fit=crop',
        ]);

        $prefixes = [
            'Deluxe Room' => 'D',
            'Executive Suite' => 'E',
            'Standard Room' => 'S',
            'Presidential Suite' => 'P',
            'Junior Suite' => 'J',
            'Family Room' => 'F',
            'Superior Room' => 'U',
        ];

        // Create rooms for each type
        foreach ([$deluxe, $suite, $standard, $presidential, $junior, $family, $superior] as $type) {
            $prefix = $prefixes[$type->name] ?? substr($type->name, 0, 1);
            for ($i = 1; $i <= 5; $i++) {
                Room::create([
                    'room_type_id' => $type->id,
                    'room_number' => $prefix . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'status' => 'available',
                ]);
            }
        }
    }
}
