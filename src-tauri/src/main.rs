// Prevents additional console window on Windows in release, DO NOT REMOVE!!
#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

// Learn more about Tauri commands at https://tauri.app/v1/guides/features/command
#[tauri::command]
fn get_root_dir() -> String {
    match project_root::get_project_root() {
        Ok(p) => format!("{:?}", p),
        Err(e) => format!("Error obtaining project root {:?}", e)
    }
}

fn main() {
    tauri::Builder::default()
        .plugin(tauri_plugin_sql::Builder::default().build())
        .invoke_handler(tauri::generate_handler![get_root_dir])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}