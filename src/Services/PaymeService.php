<?php

namespace Khamdullaevuz\Payme\Services;

use Khamdullaevuz\Payme\Enums\PaymeState;
use Khamdullaevuz\Payme\Exceptions\PaymeException;
use Khamdullaevuz\Payme\Models\PaymeTransaction;
use App\Models\User;
use Khamdullaevuz\Payme\Traits\JsonRPC;
use Khamdullaevuz\Payme\Traits\PaymeHelper;
use Illuminate\Http\JsonResponse;

class PaymeService
{
    use JsonRPC, PaymeHelper;
    protected int $minAmount = 1_000_00;
    protected int $maxAmount = 100_000_000_00;

    /**
     * Transaction timeout
     *
     * @var int $timeout
     */
    protected int $timeout = 6000 * 1000;

    protected string $identity = 'id';

    public function __construct(public array $params)
    {
        $this->minAmount = config('payme.min_amount', $this->minAmount);
        $this->maxAmount = config('payme.max_amount', $this->maxAmount);
        $this->identity = config('payme.identity', $this->identity);
    }

    /**
     * @throws PaymeException
     */
    public function CheckPerformTransaction(): JsonResponse
    {
        if(!$this->hasParam(['amount', 'account']))
        {
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }
        $amount = $this->params['amount'];

        if(!$this->isValidAmount($amount))
        {
            throw new PaymeException(PaymeException::WRONG_AMOUNT);
        }

        $account = $this->params['account'];
        if(!array_key_exists($this->identity, $account))
        {
            throw new PaymeException(PaymeException::USER_NOT_FOUND);
        }

        $account = $account[$this->identity];

        $user = User::where($this->identity, $account)->first();

        if(!$user)
        {
            throw new PaymeException(PaymeException::USER_NOT_FOUND);
        }

        return $this->successCheckPerformTransaction();
    }

    /**
     * @throws PaymeException
     */
    public function CreateTransaction(){
        if(!$this->hasParam(['id', 'time', 'amount', 'account']))
        {
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }
        $id = $this->params['id'];
        $time = $this->params['time'];
        $amount = $this->params['amount'];
        $account = $this->params['account'];

        if(!array_key_exists($this->identity, $account))
        {
            throw new PaymeException(PaymeException::USER_NOT_FOUND);
        }

        $account = $account[$this->identity];

        $user = User::where($this->identity, $account)->first();

        if(!$user)
        {
            throw new PaymeException(PaymeException::USER_NOT_FOUND);
        }

        if(!$this->isValidAmount($amount))
        {
            throw new PaymeException(PaymeException::WRONG_AMOUNT);
        }

        $transaction = PaymeTransaction::where('transaction', $id)->first();

        if($transaction)
        {
            if ($transaction->state != PaymeState::Pending) {
                throw new PaymeException(PaymeException::CANT_PERFORM_TRANS);
            }

            if(!$this->checkTimeout($transaction->create_time))
            {
                $transaction->update([
                    'state' => PaymeState::Cancelled,
                    'reason' => 4
                ]);

                throw new PaymeException(error: PaymeException::CANT_PERFORM_TRANS, customMessage: [
                    "uz" => "Vaqt tugashi o'tdi",
                    "ru" => "Тайм-аут прошел",
                    "en" => "Timeout passed"
                ]);
            }

            return $this->successCreateTransaction(
                $transaction->create_time,
                $transaction->id,
                $transaction->state
            );
        }

        $transaction = PaymeTransaction::create([
            'transaction' => $id,
            'payme_time' => $time,
            'amount' => $amount,
            'state' => PaymeState::Pending,
            'create_time' => $this->microtime(),
            'owner_id' => $account,
        ]);

        return $this->successCreateTransaction(
            $transaction->create_time,
            $transaction->id,
            $transaction->state
        );
    }

    /**
     * @throws PaymeException
     */
    public function PerformTransaction(){
        if(!$this->hasParam('id'))
        {
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }
        $id = $this->params['id'];

        $transaction = PaymeTransaction::where('transaction', $id)->first();
        if(!$transaction)
        {
            throw new PaymeException(PaymeException::TRANS_NOT_FOUND);
        }

        if($transaction->state !== PaymeState::Pending){
            if($transaction->state == PaymeState::Done)
            {
                return $this->successPerformTransaction($transaction->state, $transaction->perform_time, $transaction->id);
            }else{
                throw new PaymeException(PaymeException::CANT_PERFORM_TRANS);
            }
        }

        if(!$this->checkTimeout($transaction->create_time))
        {
            $transaction->update([
                'state' => PaymeState::Cancelled,
                'reason' => 4
            ]);
            throw new PaymeException(error: PaymeException::CANT_PERFORM_TRANS, customMessage: [
                "uz" => "Vaqt tugashi o'tdi",
                "ru" => "Тайм-аут прошел",
                "en" => "Timeout passed"
            ]);
        }

        $transaction->state = PaymeState::Done;
        $transaction->perform_time = $this->microtime();
        $transaction->save();

        $this->fillUpBalance($transaction->user, $transaction->amount);

        return $this->successPerformTransaction($transaction->state, $transaction->perform_time, $transaction->id);
    }

    /**
     * @throws PaymeException
     */
    public function CancelTransaction(){
        if(!$this->hasParam(['id', 'reason']))
        {
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }
        if(!array_key_exists('reason', $this->params))
        {
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }

        $id = $this->params['id'];
        $reason = $this->params['reason'];

        $transaction = PaymeTransaction::where('transaction', $id)->first();
        if(!$transaction){
            throw new PaymeException(PaymeException::TRANS_NOT_FOUND);
        }

        if ($transaction->state == PaymeState::Pending) {
            $cancelTime = $this->microtime();
            $transaction->update([
                "state" => PaymeState::Cancelled,
                "cancel_time" => $cancelTime,
                "reason" => $reason
            ]);

            return $this->successCancelTransaction($transaction->state, $cancelTime, $transaction->id);
        }

        if ($transaction->state != PaymeState::Done) {
            return $this->successCancelTransaction($transaction->state, $transaction->cancel_time, $transaction->id);
        }

        $this->withdrawBalance($transaction->user, $transaction->amount);

        $cancelTime = $this->microtime();

        $transaction->update([
            "state" => PaymeState::Cancelled_After_Success,
            "cancel_time" => $cancelTime,
            "reason" => $reason
        ]);

        return $this->successCancelTransaction($transaction->state, $cancelTime, $transaction->id);
    }


    /**
     * @throws PaymeException
     */
    public function CheckTransaction(){
        if(!$this->hasParam('id'))
        {
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }

        $id = $this->params['id'];

        $transaction = PaymeTransaction::where('transaction', $id)->first();

        if($transaction)
        {
            return $this->successCheckTransaction(
                $transaction->create_time,
                $transaction->perform_time,
                $transaction->cancel_time,
                $transaction->id,
                $transaction->state,
                $transaction->reason
            );
        }else{
            throw new PaymeException(PaymeException::TRANS_NOT_FOUND);
        }
    }

    public function GetStatement(){
        // pass
    }

    public function SetFiscalData(){
        // pass
    }
}