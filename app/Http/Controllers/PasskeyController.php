<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Passkey;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
                'credential_id' => $request->publicKeyCredentialSource['credential']['id'],
                'public_key' => $request->publicKeyCredentialSource['jsonData'],
            ]);
        }
    }

    /**
     * Get the autnentication options
     */
    public function getAuthenticationOptions(PasskeyAuthenticator $passkeyAuthenticator, Request $request)
    {
        return back()->with('flash', [
            'options' => User::whereEmail($request->email)->first()->passkeys->count() ? tap($passkeyAuthenticator->generateOptions(User::whereEmail($request->email)->first()), fn($options) => logger(json_encode($options))) : false,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function verify(VerifyPasskeyRequest $request, Passkey $passkey)
    {
        if(isset(PasskeyTool::$updateModelCallback) && is_callable(PasskeyTool::$updateModelCallback)) {
            app()->call(PasskeyTool::$updateModelCallback);
        }
        else
        {
            $request->user()->passkeys()->credential($request->publicKeyCredentialSource['credentialId'])->update([
                'public_key' => $request->publicKeyCredentialSource['jsonData'],
            ]);
        }

        return back()->with('flash', [
            'verified' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function login(VerifyPasskeyRequest $request, Passkey $passkey)
    {
        Log::debug('Request received');
        if(isset(PasskeyTool::$updateModelCallback) && is_callable(PasskeyTool::$updateModelCallback)) {
            Log::debug('Overriding login function');
            app()->call(PasskeyTool::$updateModelCallback);
        }
        else
        {
            Log::debug('updating user');
            Passkey::credential($request->publicKeyCredentialSource['credentialId'])
                ->user($request->publicKeyCredentialSource['userHandle'])
                ->update([
                    'public_key' => $request->publicKeyCredentialSource['jsonData'],
                ]);
        }
        Log::debug('logging in user');
        Auth::loginUsingId($request->publicKeyCredentialSource['userHandle']);

        return redirect()->intended(route('bills.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Passkey $passkey)
    {
        $passkey->delete();
    }
}
