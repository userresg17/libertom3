<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\GiftCard;
// TODO: Importar GiftCardService, FormRequests (Purchase, Redeem), User, Wallet, Transaction

class GiftCardController extends Controller
{
    /**
     * Exibe a página de gerenciamento de Gift Cards.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Carteiras disponíveis para pagamento
        $userWallets = $user->wallets()->where('is_active', true)->where('balance', '>', 0)->get();

        // Gift cards do usuário (recebidos ou comprados para si)
        $myGiftCards = GiftCard::where(function($query) use ($user) {
                                $query->where('recipient_email', $user->email) // Recebidos por email
                                      ->orWhere('purchased_by_user_id', $user->id)->whereNull('recipient_email'); // Comprados para si
                            })
                            ->orWhere('redeemed_by_user_id', $user->id) // Ou já resgatados por ele
                            ->latest()
                            ->get(); // Ou paginate()

        // Gift cards enviados pelo usuário
        $sentGiftCards = GiftCard::where('purchased_by_user_id', $user->id)
                                ->whereNotNull('recipient_email')
                                ->latest()
                                ->get(); // Ou paginate()


        return view('dashboard.giftcards', compact('user', 'userWallets', 'myGiftCards', 'sentGiftCards'));
    }

    /**
     * Processa a compra de um Gift Card.
     */
    // public function purchase(PurchaseGiftCardRequest $request): RedirectResponse {
    //     // 1. Validar (Request)
    //     // 2. Verificar saldo da carteira de pagamento
    //     // 3. Verificar senha de transação
    //     // 4. Debitar valor da carteira do usuário (criar transação)
    //     // 5. Chamar GiftCardService->create(...) para gerar o gift card
    //     // 6. Se tiver destinatário, chamar GiftCardService->sendToRecipient(...) (enviar email)
    //     // 7. Logar ação
    //     // 8. Redirecionar com sucesso
    // }

    /**
     * Processa o resgate de um Gift Card.
     */
    // public function redeem(RedeemGiftCardRequest $request): RedirectResponse {
    //     // 1. Validar código (Request)
    //     // 2. Buscar GiftCard pelo código
    //     // 3. Verificar se pode ser resgatado (GiftCard->canBeRedeemed())
    //     // 4. Chamar GiftCardService->redeem(...) -> credita na wallet do usuário, atualiza status do card, cria transação
    //     // 5. Logar ação
    //     // 6. Redirecionar com sucesso
    // }

}