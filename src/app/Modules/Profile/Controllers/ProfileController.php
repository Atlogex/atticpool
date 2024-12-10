<?php declare(strict_types=1);

namespace App\Modules\Profile\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Profile\Resources\ProfileResource;
use App\Modules\Profile\Models\Profile;
use App\Modules\Profile\Repositories\ProfileRepository;
use GuzzleHttp\Psr7\Request;

class ProfileController extends Controller
{
    private ProfileRepository $repository;

    public function __construct(ProfileRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        return ProfileResource::collection($this->repository->all());
    }

    public function show(Profile $profile)
    {
        return new ProfileResource($profile);
    }

    public function register(Request $request)
    {
        dump($request->getBody()->getContents());
        $githubUser = json_decode($request->getBody()->getContents());

        $user = User::updateOrCreate([
            'github_id' => $githubUser->id,
        ], [
            'name'                 => $githubUser->name,
            'email'                => $githubUser->email,
            'github_token'         => $githubUser->token,
            'github_refresh_token' => $githubUser->refreshToken,
        ]);

        Auth::login($user);

        return view('profile::register');
    }
}
