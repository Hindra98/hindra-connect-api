<?php

namespace App\Models;

use App\Repositories\ParamsRepository;
use App\Repositories\ProfileRepository;

class ClaimJwt
{
  public User $user;
  public int $exp;

  public function __construct(User $user, int $exp)
  {
    $this->user = $user;
    $this->exp = $exp;
  }
  public function setData(User $user, int $exp)
  {
    $profile = (new ProfileRepository())->getByUser($this->user->id);
    $params = (new ParamsRepository())->getByUser($this->user->id);
    return [
      'userName' => $user->email,
      'userId' => $user->id,
      'role' => $user->role,
      'fullname' => $profile->firstname + " "+$profile->lastname,
      'userlanguage' => $params->userlanguage,
      'exp' => $exp
    ];
  }
}
