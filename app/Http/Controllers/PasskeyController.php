<?php
namespace App\Http\Controllers;

use App\Models\Passkey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StorePasskeyRequest;
use App\Contracts\Passkey\PasskeyRegistrar;
use App\Http\Requests\UpdatePasskeyRequest;
use Psr\Http\Message\ServerRequestInterface;
use App\Tools\Passkey\Passkey as PasskeyTool;
use Illuminate\Validation\ValidationException;

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
     * Show the form for editing the specified resource.
     */
    public function authenticationOptions()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function authenticate(UpdatePasskeyRequest $request, Passkey $passkey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Passkey $passkey)
    {
        //
    }
}
