<?php
function safeHtmlOutput($content) {
    // 1. 1차적으로 위험한 스크립트 관련 태그 및 이벤트 속성 제거
    $patterns = [
        '/<script\b[^>]*>(.*?)<\/script>/is', // script 태그 전체
        '/<iframe\b[^>]*>(.*?)<\/iframe>/is', // iframe 태그 전체
        '/<object\b[^>]*>(.*?)<\/object>/is', // object 태그 전체
        '/<embed\b[^>]*>(.*?)<\/embed>/is',   // embed 태그 전체
        '/\s+on\w+\s*=\s*["\'][^"\']*["\']/i' // onclick, onload 등의 이벤트 속성
    ]; 
    $filtered = preg_replace($patterns, '', $content);
    // 2. 2차 검증: 허용할 HTML 태그만 남기고 나머지는 특수문자 변환
    // <b>, <i>, <u>, <br>, <p>, <img> 등 필요한 태그를 두 번째 인자에 추가하세요.
    $allowed_tags = '<b><i><u><br><p><img><h1><h2><h3><h4><h5><h6><a><ul><ol><li><div><span><strong><em><form><input><button><link><style>';
    $filtered = strip_tags($filtered, $allowed_tags);
    return $filtered;
}
?>