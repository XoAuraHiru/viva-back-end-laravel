<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Shedule;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Type\Integer;

class MovieController extends Controller
{
    public function index($type = null, $id = null)
    {

        if ($type === null && $id === null) {
            // Shows All Movies
            $movies = Movie::all();

            if ($movies->count() > 0) {
                return response()->json([
                    'status' => 200,
                    'data' => $movies
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No Movie Records Found'
                ], 404);
            }
        } elseif ($type === 'new' && $id === null) {

            // Shows New Movies

            $movies = Movie::with(['genre', 'status'])
                ->orderBy('year', 'desc')
                ->limit(10)
                ->get();


            if ($movies->count() > 0) {
                return response()->json([
                    'status' => 200,
                    'data' => $movies
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No Movie Records Found'
                ], 404);
            }
        } elseif ($type === 'latest' && $id === null) {

            // Shows Latest Movies


            $movies = Shedule::with(['movie', 'time', 'screen'])
                ->get();

            if ($movies->count() > 0) {
                return response()->json([
                    'status' => 200,
                    'data' => $movies
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No Movie Records Found'
                ], 404);
            }
        } elseif ($type === 'individual' && $id !== null) {
            // Shows Individual Movies

            $movies = Movie::with(['genre', 'status'])
                ->where('id', $id)
                ->get();

            if ($movies->count() > 0) {
                return response()->json([
                    'status' => 200,
                    'data' => $movies
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No Movie Records Found'
                ], 404);
            }
        } else {

            // If parameters are wrong

            return response()->json([
                'status' => 404,
                'message' => 'the request movie type parameter is wrong'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|max:255',
                'year' => 'required|date_format:Y',
                'genre' => 'required|array',
                'description' => 'required|max:255',
                'banner' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            DB::beginTransaction();

            if ($request->banner) {
                $imageName = time() . '.' . $request->banner->extension();
                $path = $request->banner->storeAs('banners', $imageName, 'public');
            } else {
                $path = null;
            }

            $code = $this->createUniqueCode();

            try {
                $movie = Movie::create([
                    'name' => $validated['name'],
                    'year' => $validated['year'],
                    'description' => $validated['description'],
                    // TODO: Add a method for inputing cast later
                    'cast' => NULL,
                    'banner_img' => '/storage/' . $path,
                    'code' => $code,
                ]);
                
                $movie->genres()->attach($validated['genre']);

                DB::commit();
            } catch (Exception $exception) {
                DB::rollback();
                return response()->json(['error' => $exception->getMessage()], 500);
            }

            return response()->json(['success' => 'Movie saved successfully'], 200);
        } catch (Exception $exception) {
            DB::rollback();
            Log::error("Error updating movie: " . $exception->getMessage());
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }




    public function deleteMovies($id)
    {

        $deleted = DB::table('movie')->where('id', '=', $id)->delete();

        return response()->json(['success' => 'Movie deleted successfully']);
    }

    public function getIndividualMovies($id)
    {

        $movie = DB::table('movie')
            ->where('id', '=', $id)
            ->get();

        return response()->json($movie);
    }

    public function updateMovies(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'id' => 'required|integer|exists:movie,id',
                'name' => 'required|string|max:255',
                'year' => 'required|integer',
                'genre' => 'required|integer',
                'description' => 'required|string',
                'banner' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            ]);

            // Start the transaction
            DB::beginTransaction();

            // Handle the banner image upload
            $imageName = time() . '.' . $request->banner->extension();
            $path = $request->banner->storeAs('banners', $imageName, 'public');

            // Update movie details
            $update = DB::table('movie')
                ->where('id', $request->id)
                ->update([
                    'name' => $request->name,
                    'year' => $request->year,
                    'genre_id' => $request->genre,
                    'description' => $request->description,
                    'banner_img' => '/storage/' . $path,
                ]);

            if (!$update) {
                throw new Exception('Failed to update movie details.');
            }

            // Commit the transaction
            DB::commit();

            return response()->json(['success' => "Movie with ID $request->id updated successfully"]);
        } catch (Exception $exception) {
            // Rollback the transaction in case of error
            DB::rollback();
            Log::error("Error updating movie: " . $exception->getMessage());
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function suggestedMovies($id)
    {

        // Get the genre_id of the movie with the given ID
        $genre_id = DB::table('movie')
            ->where('movie.id', $id)
            ->value('genre_id');

        // Get the movies with the same genre
        $suggested_movies = DB::table('movie')
            ->join('genre', 'movie.genre_id', '=', 'genre.id')
            ->where('genre_id', $genre_id)
            ->where('movie.id', '<>', $id) // Exclude the movie with the given ID
            ->select('movie.*', 'genre.genre')
            ->get();

        return $suggested_movies;
    }

    public function createUniqueCode()
    {
        $code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
        if (Movie::where('code', $code)->exists()) {
            return $this->createUniqueCode();
        } else {
            return $code;
        }
    }
}
