<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Passkey;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StorePasskeyRequest;
use App\Contracts\Passkey\PasskeyRegistrar;
use App\Http\Requests\UpdatePasskeyRequest;
use App\Http\Requests\VerifyPasskeyRequest;
use Psr\Http\Message\ServerRequestInterface;
use App\Tools\Passkey\Passkey as PasskeyTool;
use Illuminate\Validation\ValidationException;
use App\Contracts\Passkey\PasskeyAuthenticator;

class PasskeyController extends Controller
{
    public function getSessionKey()
    {
        return config('passkey.session');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return app('passkey.views.management');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getRegistrationOptions(PasskeyRegistrar $passkeyRegistrar, Request $request)
    {
        return back()->with('flash', [
            'options' => tap($passkeyRegistrar->generateOptions($request->user()), fn($options) => logger(json_encode($options))),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePasskeyRequest $request)
    {
        if(isset(PasskeyTool::$createModelCallback) && is_callable(PasskeyTool::$createModelCallback)) {
            app()->call(PasskeyTool::$createModelCallback);
        }
        else
        {
            $request->user()->passkeys()->create([
                'name' => $request->name,
                'credential_id' => $request->publicKeyCredentialSource['credentialId'],
                'public_key' => $request->publicKeyCredentialSource['jsonData'],
            ]);
        }
    }

    /**
     * Get the autnentication options
     */
    public function getAuthenticationOptions(PasskeyAuthenticator $passkeyAuthenticator, Request $request)
    {
        $email = optional($request->user())->email ?? $request->email;

        $user = User::whereEmail($email)->first();

        return back()->with('flash', [
            'options' => $user->passkeys->count() 
                            ? tap(
                                $passkeyAuthenticator->generateOptions($user), 
                                fn($options) => Log::debug("Generated options", compact('options'))) 
                            : false,
        ]);
    }

    /**
     * Verify user using passkey
     */
    public function verify(VerifyPasskeyRequest $request, Passkey $passkey)
    {
        if(isset(PasskeyTool::$updateModelCallback) && is_callable(PasskeyTool::$updateModelCallback)) {
            app()->call(PasskeyTool::$updateModelCallback);
        }
        else
        {
            Log::debug('updating user');
            $pk = json_decode($request->publicKeyCredentialSource, true);
            Passkey::credential($pk['credential']['id'])
                ->user($pk['userHandle'])
                ->update([
                    'public_key' => $request->publicKeyCredentialSource,
                ]);
        }

        $request->session()->passwordConfirmed();

        return back()->with('flash', [
            'verified' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function login(VerifyPasskeyRequest $request, Passkey $passkey)
    {
        if(isset(PasskeyTool::$updateModelCallback) && is_callable(PasskeyTool::$updateModelCallback)) {
            app()->call(PasskeyTool::$updateModelCallback);
        }
        else
        {
            $pk = json_decode($request->publicKeyCredentialSource, true);
            Passkey::credential($pk['credential']['id'])
                ->user($pk['userHandle'])
                ->update([
                    'public_key' => $request->publicKeyCredentialSource,
                ]);
        }
        Auth::loginUsingId($pk['userHandle'], $request->remember == 'on');

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Passkey $passkey)
    {
        $passkey->delete();
    }
}
