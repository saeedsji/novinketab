<?php

namespace App\Lib\User;

use App\Models\User;

class UserProfileClass
{
    private $user, $fieldCount;

    private $fields = ['name', 'gender', 'birthday', 'weight', 'height', 'marital'];

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->fieldCount = count($this->fields);
    }

    public function isCompelete()
    {
        return $this->fieldCount === $this->compeleteCount();
    }


    public function completePercent()
    {
        return (round($this->compeleteCount() / $this->fieldCount * 100));
    }

    private function compeleteCount()
    {
        $count = 0;
        foreach ($this->fields as $field) {
            if (!empty($this->user->$field))
                $count += 1;
        }
        return $count;
    }
}
