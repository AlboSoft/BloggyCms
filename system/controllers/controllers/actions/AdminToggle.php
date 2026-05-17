<?php

namespace controllers\actions;

class AdminToggle extends ControllersAction {
    
    public function execute() {
        header('Content-Type: application/json');
        
        $controller = $_POST['controller'] ?? null;
        $enabled = $_POST['enabled'] ?? null;
        
        if (!$controller) {
            echo json_encode(['success' => false, 'message' => 'Controller name missing']);
            return;
        }
        
        try {
            $tableName = $this->db->getPrefix() . 'settings';
            $key = 'controller_' . $controller;
            
            $newValue = ($enabled == '1' || $enabled === 1 || $enabled === true) ? 1 : 0;
            
            $existing = $this->db->fetch(
                "SELECT id, settings FROM `{$tableName}` WHERE group_key = ?",
                [$key]
            );
            
            if ($existing) {
                $settings = json_decode($existing['settings'], true);
                if (!is_array($settings)) {
                    $settings = [];
                }
                $settings['enabled'] = $newValue;
                
                $this->db->query(
                    "UPDATE `{$tableName}` SET settings = ?, updated_at = NOW() WHERE id = ?",
                    [json_encode($settings), $existing['id']]
                );
            } else {
                $settings = ['enabled' => $newValue];
                $this->db->query(
                    "INSERT INTO `{$tableName}` (group_key, settings) VALUES (?, ?)",
                    [$key, json_encode($settings)]
                );
            }
            
            \SettingsHelper::clearCache($key);
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            
            echo json_encode(['success' => true]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}