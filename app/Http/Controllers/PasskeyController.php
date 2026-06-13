<?php namespace App\Http\Controllers;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Requests\Passkey\LoginRequest;
use App\Http\Requests\Passkey\StoreRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Notsoweb\ApiResponse\Enums\ApiResponse;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Events\PasskeyUsedToAuthenticateEvent;
use Spatie\LaravelPasskeys\Models\Passkey;
use Spatie\LaravelPasskeys\Support\Config;
use Throwable;

/**
 * Controlador de passkeys
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class PasskeyController extends Controller
{
    /**
     * Listar passkeys del usuario autenticado
     */
    public function index()
    {
        return ApiResponse::OK->response([
            'passkeys' => Auth::user()->passkeys()
                ->select('id', 'name', 'last_used_at', 'created_at')
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Generar opciones de registro de passkey
     */
    public function registerOptions()
    {
        $action = Config::getAction(
            'generate_passkey_register_options',
            GeneratePasskeyRegisterOptionsAction::class,
        );

        return ApiResponse::OK->response([
            'options' => $action->execute(Auth::user()),
        ]);
    }

    /**
     * Registrar passkey
     */
    public function store(StoreRequest $request)
    {
        $storePasskeyAction = Config::getAction(
            'store_passkey',
            StorePasskeyAction::class,
        );

        try {
            $passkey = $storePasskeyAction->execute(
                Auth::user(),
                $request->input('passkey'),
                $request->input('options'),
                $this->relyingPartyHost(),
                ['name' => $request->input('name')],
            );
        } catch (Throwable) {
            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'passkey' => [__('passkeys::passkeys.error_something_went_wrong_generating_the_passkey')],
            ]);
        }

        return ApiResponse::OK->response([
            'passkey' => $passkey->only(['id', 'name', 'last_used_at', 'created_at']),
        ]);
    }

    /**
     * Eliminar passkey
     */
    public function destroy(Passkey $passkey)
    {
        if ($passkey->authenticatable_id !== Auth::id()) {
            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'passkey' => [__('passkeys::passkeys.invalid')],
            ]);
        }

        $passkey->delete();

        return ApiResponse::OK->response([
            'deleted' => true,
        ]);
    }

    /**
     * Generar opciones de autenticación con passkey
     */
    public function authenticationOptions()
    {
        $action = Config::getAction(
            'generate_passkey_authentication_options',
            GeneratePasskeyAuthenticationOptionsAction::class,
        );

        return ApiResponse::OK->response([
            'options' => $action->execute(),
        ]);
    }

    /**
     * Iniciar sesión con passkey
     */
    public function login(LoginRequest $request)
    {
        $passkeyOptions = $request->input('options') ?? Session::pull('passkey-authentication-options');

        if (blank($passkeyOptions)) {
            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'passkey' => [__('passkeys::passkeys.invalid')],
            ]);
        }

        $findPasskeyAction = Config::getAction(
            'find_passkey',
            FindPasskeyToAuthenticateAction::class,
        );

        $passkey = $findPasskeyAction->execute(
            $request->input('start_authentication_response'),
            $passkeyOptions,
        );

        if (! $passkey?->authenticatable) {
            return ApiResponse::UNPROCESSABLE_CONTENT->response([
                'passkey' => [__('passkeys::passkeys.invalid')],
            ]);
        }

        $user = $passkey->authenticatable;

        event(new PasskeyUsedToAuthenticateEvent($passkey, $request));

        return ApiResponse::OK->response([
            'user' => $user,
            'token' => $user->createToken(config('app.slug'))->accessToken,
        ]);
    }

    /**
     * Host del relying party para validar la ceremonia WebAuthn
     */
    protected function relyingPartyHost(): string
    {
        return parse_url(config('app.frontend.url'), PHP_URL_HOST)
            ?: parse_url(config('app.url'), PHP_URL_HOST);
    }
}
