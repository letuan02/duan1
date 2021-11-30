<?php

    require_once '../../global.php';
    require_once '../../dao/product.php';
    require_once '../../dao/user.php';
    require_once '../../dao/order.php';
    require_once '../../dao/analytic.php';
    require_once '../../dao/contact.php';

    check_login();
    extract($_REQUEST);

    if (array_key_exists('chart', $_REQUEST)) {
        // thống kê đơn hàng mới, đã xác nhận, đã hủy
        $totalOrderNew = analytics_select_totalOrder_by_stt(0); //đơn mới
        $totalOrderVerified = analytics_select_totalOrder_by_stt(1); //đã xác nhận
        $totalOrderShip = analytics_select_totalOrder_by_stt(2); //đang giao
        $totalOrderSuccess = analytics_select_totalOrder_by_stt(3); //đã giao
        $totalOrderCancel = analytics_select_totalOrder_by_stt(4); //đã hủy
        $totalOrder = $totalOrderNew + $totalOrderVerified + $totalOrderShip + $totalOrderSuccess + $totalOrderCancel;

        // thống kê loại hàng
        $quantityAnalytics = analytics_quantity_product_by_cate();

        // thống kê user
        $userAnalytics = analytics_user();

        // thống kê doanh thu
        $priceAnalytics = analytics_price();

        // tổng sp hiện có
        $totalProduct = count(product_select_all());

        // tổng tài khoản
        $totalUser = count(user_select_all());

        // tổng doanh thu
        $totalMoney = analytics_total_money();
        // var_dump($totalMoney);

        // thống kê kh đky theo tháng
        $userRegAnalytics = analytics_user_reg();
        $VIEW_PAGE = "chart.php";
    }else if(array_key_exists('feedback', $_REQUEST)){
        $title = 'Góp ý';
        $limit = 10;
        $totalUser = count(contact_select_all());
        $totalPage = ceil($totalUser / $limit);
        $currentPage = $page ?? 1;
        
        if ($currentPage > $totalPage) {
            $currentPage = $totalPage;
        } else if ($currentPage < 0) {
            $currentPage = 1;
        }
        
        $start = ($currentPage - 1) * $limit;

        $items = contact_select_all();
        $VIEW_PAGE = 'gop-y.php';
        $VIEW_PAGE="feedback.php";
    }else if(array_key_exists('btn_delete', $_REQUEST )){
        contact_delete($id);
        header('location: '. $ADMIN_URL.'/analytics?feedback');
    }else {
        $quantityAnalytics = analytics_quantity_product_by_cate();
        $priceAnalytics = analytics_price_product_by_cate();
        
        $dataAnalytics = [];
        foreach ($quantityAnalytics as $qnt) {
            foreach ($priceAnalytics as $price) {
                if ($qnt['id'] == $price['id']) {
                    $newArr = array_merge($qnt, $price);
                    array_push($dataAnalytics, $newArr);
                }
            }
        }

        $VIEW_PAGE = "list.php";
    }

    require_once '../layout.php';

?>