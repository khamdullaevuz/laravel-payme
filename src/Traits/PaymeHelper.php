<?php

namespace Khamdullaevuz\Payme\Traits;

use Khamdullaevuz\Payme\Exceptions\PaymeException;

trait PaymeHelper
{
    protected function microtime(): int
    {
        return (time() * 1000);
    }

    private function checkTimeout($created_time): bool
    {
        return $this->microtime() <= ($created_time + $this->timeout);
    }

    public function isValidAmount($amount): bool
    {
        if ($amount < $this->minAmount || $amount > $this->maxAmount) {
            return false;
        }

        return true;
    }

    public function successCreateTransaction($createTime, $transaction, $state)
    {
        return $this->success([
            'create_time' => $createTime,
            'perform_time' => 0,
            'cancel_time' => 0,
            'transaction' => strval($transaction),
            'state' => $state,
            'reason' => null
        ]);
    }

    public function successCheckPerformTransaction()
    {
        return $this->success([
            "allow" => true
        ]);
    }

    public function successPerformTransaction($state, $performTime, $transaction)
    {
        return $this->success([
            "state" => $state,
            "perform_time" => $performTime,
            "transaction" => strval($transaction),
        ]);
    }

    public function successCheckTransaction($createTime, $performTime, $cancelTime, $transaction, $state, $reason)
    {
        return $this->success([
            "create_time" => $createTime ?? 0,
            "perform_time" => $performTime ?? 0,
            "cancel_time" => $cancelTime ?? 0,
            "transaction" => strval($transaction),
            "state" => $state,
            "reason" => $reason
        ]);
    }

    public function successCancelTransaction($state, $cancelTime, $transaction)
    {
        return $this->success([
            "state" => $state,
            "cancel_time" => $cancelTime,
            "transaction" => strval($transaction)
        ]);
    }

    public function fillUpBalance($user, $amount): void
    {
        $user->balance += $amount;
        $user->save();
    }

    public function withdrawBalance($user, $amount): void
    {
        $user->balance -= $amount;
        $user->save();
    }

    public function hasParam($param): bool
    {
        if (is_array($param)) {
            foreach ($param as $item) {
                if(!$this->hasParam($item)) return false;
            }
            return true;
        } else {
            return isset($this->params[$param]) && !empty($this->params[$param]);
        }
    }
}