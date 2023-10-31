<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BidLog;
use App\Models\Listing;
use App\Models\Car;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class BidLogController extends Controller
{
    public function createBidLog(array $bidLogData) {
        BidLog::insert($bidLogData);
    }

    public function ShowUserBids(Request $req) {
        $userId = Auth::user()->id;

        $bids = BidLog::where('bidder_id', $userId)
        ->get();
    
        // Use a collection to filter out duplicates based on listing_id
        $uniqueBids = $bids->unique('listing_id');
        
        // Extract the listing_id values into an array
        $listingIds = $uniqueBids->pluck('listing_id')->toArray();

        // Retrieve listings with the extracted listing_ids
        $listings = Listing::whereIn('id', $listingIds)
                            ->where('expires_at', '>', now())
                            ->get();
        
        // Retrieve expired listings
        $expiredListings = Listing::where('expires_at', '<=', now())
                            ->get();

        $listingController = new ListingController();
        $listingController->processExpiredListings($expiredListings);

        // Extract the car_id values into an array
        $carIds = $listings->pluck('car_id')->toArray();

        // Retrieve cars with the extracted listing_ids
        $cars = Car::whereIn('id', $carIds)->get();

        // Extract the user_id values into an array
        $userIds = $cars->pluck('seller_id')->toArray();

        // Retrieve user with the extracted listing_ids
        $users = User::whereIn('id', $userIds)->get();

        // Process the results as before
        $carListings = [];

        $listingController = new ListingController();
            
        foreach ($listings as $listing) {
            $matchingCar = $cars->where('id', $listing->car_id)->first();
            $mainImagePath = "";

            if ($matchingCar) {
                $matchingUser = $users->where('id', $matchingCar->seller_id)->first();
                $mainImageDirectory = "storage/uploads/{$matchingUser->name} {$matchingUser->surname}/{$matchingCar->make} {$matchingCar->model} {$matchingCar->year}/main-image";

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                // Search for an image file in the directory
                foreach ($allowedExtensions as $extension) {
                    $imagePath = "{$mainImageDirectory}/car-main-image.{$extension}";
                    if (file_exists($imagePath)) {
                        $mainImagePath = $imagePath;
                        break; // Stop searching once an image is found
                    }
                }

                $timeRemaining = $listingController->getRemainingTime($listing->expires_at);

                if ($matchingUser) {
                    $combined = [
                        'listing' => $listing,
                        'car' => $matchingCar,
                        'user' => $matchingUser,
                        'imagePath' => $mainImagePath,
                        'remainingHours' => $timeRemaining['hours'],
                        'remainingMinutes' => $timeRemaining['minutes'],
                        'remainingSeconds' => $timeRemaining['seconds'],
                    ];
                    $carListings[] = $combined;
                }
            }
        }

        // Convert $carListings array to a collection
        $carListingsCollection = collect($carListings);

        // Define the number of items to display per page (e.g., 4 items per page)
        $perPage = 4; // You can adjust this to your desired value

        // Create a LengthAwarePaginator instance without appending query parameters
        $paginatedCarListings = new \Illuminate\Pagination\LengthAwarePaginator(
            $carListingsCollection->forPage(request('page'), $perPage),
            $carListingsCollection->count(),
            $perPage,
            null,
            ['path' => route('user.bids')] // Use the correct route name or URL
        );

        // Pass the filtered and paginated results to the view
        return view("user-bids", [
            'carListings' => $paginatedCarListings,
        ]);
    }

    public function showCarBidLog(Request $req, int $id) {
        $bids = BidLog::where('car_id', $id)->get();

        $carBidLog = [];

        foreach ($bids as $bid) {
            $car = Car::find($bid->car_id);
            $listing = Listing::find($bid->listing_id);
            $bidder = User::find($bid->bidder_id);
            
            $carBidLog[] = [
                'car' => $car,
                'listing' => $listing,
                'bidder' => $bidder,
                'bid' => $bid,
            ];
        }

        $iterator = 1;

        return view("car-bid-log", ['carBidLog' => $carBidLog, 'iterator' => $iterator]);
    }
}
