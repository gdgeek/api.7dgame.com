<?php
// �������ݸ�ǰ��
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://127.0.0.1'); // �����޸����������ʵ���վ
header('Access-Control-Allow-Headers: origin,accept,content-type');
echo json_encode($keys);
