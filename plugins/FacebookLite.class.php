<?php

/*
	Class operate depended on Graph API v2.3
	Reference
		https://developers.facebook.com/docs/graph-api/common-scenarios
		https://developers.facebook.com/docs/graph-api/reference/v2.3/user/feed
*/

Class FacebookLite
{
    public $error = '';

    public function getUser($accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me?access_token=' . $accessToken);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $fbme = json_decode($response, true);
        if (!isset($fbme['id']) || !isset($fbme['verified']) and $fbme['verified'] != 1) {
            $this->error = 'Facebook return invalid info with token [' . $accessToken . ']'
			return false;
		}
        return $fbme;
    }

    public function getFriends($accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me/friends?limit=100000&offset=0&access_token=' . $accessToken);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $fbFriends = json_decode($response, true);
        if (empty($fbFriends['data'])) {
            $this->error = 'Facebook return invalid data with token [' . $option['access_token'] . ']';
            return false;
        }
        $friends = array();
        foreach ($fbFriends['data'] as $data) {
            $friends[$data['id']] = $data['name'];
        }
        return $friends;
    }

    /** You can determine whether two people are friends on Facebook, without having to parse their entire list of friends. */
    public function isFriend($accessToken, $friendFbid)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me/friends/' . $friendFbid . '?access_token=' . $accessToken);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $fbFriends = json_decode($response, true);
        if (empty($fbFriends['data'])) {
            return false;
        }
        return true;
    }

    public function publishLink($accessToken, $link)
    {
        $permissions = $this->getPermissions($accessToken);
        if (empty($permissions) or !in_array('publish_actions', $permissions)) {
            $this->error = 'No permission to publish feed.';
            return false;
        }
        return $this->publishAction($accessToken, array('link' => $link));
    }

    public function getPermissions($accessToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me/permissions?limit=100000&offset=0&access_token=' . $accessToken);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $fbPermission = json_decode($response, true);
        if (empty($fbPermission['data'][0])) {
            $this->error = 'Facebook return invalid data with token [' . $accessToken . ']';
            return array();
        }
        $permission = array();
        foreach ($fbPermission['data'][0] as $permissionName => $status) {
            if ($status == 'granted') {
                $permission[] = $permissionName;
            }
        }
        return $permission;
    }

    public function publishAction($accessToken, $data)
    {
        if (empty($data)) {
            $this->error = 'Publish data cannot be empty.';
            return false;
        }
        $data['access_token'] = $accessToken;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me/feed');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $fbFeed = json_decode($response, true);
        if (!empty($fbFeed['error']['message']) or empty($fbFeed['id'])) {
            $this->error = !empty($fbFeed['error']['message']) ? $fbFeed['error']['message'] : 'Facebook return invalid data with token [' . $option['access_token'] . ']';
            return false;
        }
        return $fbFeed['id'];
    }

    public function publishMessage($accessToken, $message)
    {
        $permissions = $this->getPermissions($accessToken);
        if (empty($permissions) or !in_array('publish_actions', $permissions)) {
            $this->error = 'No permission to publish feed.';
            return false;
        }
        return $this->publishAction($accessToken, array('message' => $message));
    }


}