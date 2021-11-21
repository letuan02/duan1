<?php

    require_once '../../global.php';
    require_once '../../dao/user.php';
    require_once '../../dao/order.php';
    require_once '../../dao/order_detail.php';

    // admin có thể truy cập
    check_login();

    extract($_REQUEST);

    if (array_key_exists('cart', $_REQUEST)) {
        // phân trang
        $totalOrder = count(order_select_all_by_user_id($_SESSION['user']['id']));
        $limit = 10;
        $totalPage = ceil($totalOrder / $limit);

        $currentPage = $page ?? 1;

        if ($currentPage <= 0) {
            header('Location: ' . $ADMIN_URL . '/order/?page=1');
        } else if ($currentPage > $totalPage) {
            $currentPage = $totalPage;
        }

        $start = ($currentPage - 1) * $limit;

        $myCarts = order_select_all_by_user_id($_SESSION['user']['id'], $start, $limit);
        $VIEW_PAGE = "cart_list.php";
    } else if (array_key_exists('cart_cancel', $_REQUEST)) {
        // đơn hàng đã/đang giao không thể hủy
        if (order_check_delivered($id)) {
            echo '<script>
            var result = confirm("Bạn không thể hủy đơn hàng này!")
            if (result) {
                window.location.href = "?cart_detail&id='.$id.'";
            } else {
                window.location.href = "?cart_detail&id='.$id.'";
            }
            </script>';
        } else {
            order_update_status(4, $id);
            header('Location: ' . $ADMIN_URL . '/account/?cart_detail&id=' . $id);
        }
    } else if (array_key_exists('cart_detail', $_REQUEST)) {
        // chi tiết đơn hàng
        $myCartDetail = order_detail_select_all_by_o_id($id);

        // thông tin đơn hàng
        $myCartInfo = order_select_by_id($id);
        $VIEW_PAGE = "cart_detail.php";
    } else if (array_key_exists('btn_update_pass', $_REQUEST)) {
        $password = [];
        $errorMessage = [];

        $password['old_password'] = $old_password ?? '';
        $password['new_password'] = $new_password ?? '';
        $password['confirm_password'] = $confirm_password ?? '';

        if (!$password['old_password']) {
            $errorMessage['old_password'] = 'Vui lòng nhập mật khẩu hiện tại';
        } else if (!password_verify($old_password, $_SESSION['user']['password'])) {
            $errorMessage['old_password'] = 'Vui lòng nhập lại, mật khẩu hiện tại không chính xác';
        }

        if (!$password['new_password']) {
            $errorMessage['new_password'] = 'Vui lòng nhập mật khẩu mới';
        } else if (strlen($password['new_password']) < 3) {
            $errorMessage['new_password'] = 'Vui lòng nhập mật khẩu tối thiểu 3 ký tự';
        }

        if (!$password['confirm_password']) {
            $errorMessage['confirm_password'] = 'Vui lòng nhập mật khẩu xác nhận';
        } else if (strlen($password['confirm_password']) < 3) {
            $errorMessage['confirm_password'] = 'Vui lòng nhập mật khẩu tối thiểu 3 ký tự';
        } else if ($password['new_password'] != $password['confirm_password']) {
            $errorMessage['confirm_password'] = 'Vui lòng nhập lại, mật khẩu xác nhận không chính xác';
        }

        if (empty($errorMessage)) {
            $new_password = password_hash($new_password, PASSWORD_DEFAULT);
            user_change_pass($new_password, $_SESSION['user']['id']);
            $MESSAGE = "Cập nhật mật khẩu thành công";
            
            // cập nhật session
            $user = user_select_by_id($_SESSION['user']['id']);
            $_SESSION['user'] = $user;
            unset($password);
        }

        $VIEW_PAGE = "edit_pass.php";
    } else if (array_key_exists('update_pass', $_REQUEST)) {
        $VIEW_PAGE = "edit_pass.php";
    } else if (array_key_exists('btn_update_info', $_REQUEST)) {
        $infoUser = [];
        $errorMessage = [];

        $infoUser['fullName'] = $fullName ?? '';
        $infoUser['email'] = $email ?? '';
        $infoUser['phone'] = $phone ?? '';
        $infoUser['address'] = $address ?? '';

        if (!$infoUser['fullName']) {
            $errorMessage['fullName'] = 'Vui lòng nhập họ tên của bạn';
        }

        if (!$infoUser['email']) {
            $errorMessage['email'] = 'Vui lòng nhập địa chỉ email';
        } else if (!preg_match('/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/', $infoUser['email'])) {
            $errorMessage['email'] = 'Vui lòng nhập lại, email không đúng định dạng';
        } else if ($email != $_SESSION['user']['email'] && user_exits_by_options('email', $email)) {
            // email không được trùng nhau
            $errorMessage['email'] = 'Vui lòng nhập lại, email đã tồn tại trên hệ thống';
        }

        if (!$infoUser['phone']) {
            $errorMessage['phone'] = 'Vui lòng nhập số điện thoại';
        } else if (!preg_match('/(84|0[3|5|7|8|9])+([0-9]{8})\b/', $phone)) {
            $errorMessage['phone'] = 'Vui lòng nhập lại, định dạng không chính xác';
        } else if ($phone != $_SESSION['user']['phone'] && user_exits_by_options('phone', $phone)) {
            $errorMessage['phone'] = 'Vui lòng nhập lại, sđt đã tồn tại trên hệ thống';
        }

        if (!$infoUser['address']) {
            $errorMessage['address'] = 'Vui lòng nhập địa chỉ';
        }

        if (empty($errorMessage)) {
            $user = $_SESSION['user'];
            $avatar = save_file('avatar', $IMG_PATH . '/');
            $avatar = strlen($avatar) > 0 ? $avatar : $user['avatar'];
            user_update($user['username'], $user['password'], $email, $phone, $fullName, $address, $avatar, $user['active'], $user['role'], $user['id']);
            // cập nhật session
            $user = user_select_by_id($_SESSION['user']['id']);
            $_SESSION['user'] = $user;
            $MESSAGE = 'Cập nhật thông tin tài khoản thành công';
        }

        $VIEW_PAGE = "edit_info.php";
    }  else if (array_key_exists('btn_logout', $_REQUEST)) {
        unset($_SESSION['user']);
        header('Location: ' . $SITE_URL);
    } else {
        $VIEW_PAGE = "edit_info.php";
    }

    require_once '../layout.php';

?>