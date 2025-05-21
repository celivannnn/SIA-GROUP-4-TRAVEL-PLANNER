<?php

namespace App\Services;

use App\Models\TravelForm;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TravelFormService
{
    public function getAllForUser(int $userId)
    {
        return TravelForm::where('user_id', $userId)->get();
    }

    public function getByIdForUser(int $id, int $userId)
    {
        $form = TravelForm::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$form) {
            throw new ModelNotFoundException("Travel form with ID $id not found or access denied.");
        }

        return $form;
    }

    public function createForUser(array $data, int $userId)
    {
        $data['user_id'] = $userId;
        return TravelForm::create($data);
    }

    public function updateForUser(int $id, array $data, int $userId)
    {
        $form = $this->getByIdForUser($id, $userId);
        $form->update($data);
        return $form;
    }

    public function deleteForUser(int $id, int $userId)
    {
        $form = $this->getByIdForUser($id, $userId);
        $form->delete();
        return true;
    }
}
