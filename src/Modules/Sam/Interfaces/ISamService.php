<?php
namespace LightWine\Modules\Sam\Interfaces;

use LightWine\Modules\Sam\Models\SamLoginResponseModel;
use LightWine\Modules\Sam\Models\SamUserRightsReturnModel;

interface ISamService
{
    /**
     * Gets the user role and rights
     * @return SamUserRightsReturnModel
     */
    public function GetUserRightsAssignment(): SamUserRightsReturnModel;

    /**
     * This function checks if the current user is loggedin
     * @return boolean
     */
    public function CheckIfUserIsLoggedin();

    /**
     * Login in specified username, password
     * @param string $username The username to login
     * @param string $password The password to login
     * @return SamLoginResponseModel Model containing all the information of the login
     */
    public function Login(string $username, string $password): SamLoginResponseModel;

    /**
     * This function will logoff the current user and destroy the current session
     */
    public function Logoff();
}
