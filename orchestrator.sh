#!/bin/bash

show_menu() {
    clear
    echo "=========================================="
    echo "   Dynamic Workflow Orchestrator Manager"
    echo "=========================================="
    echo "1. Run Application"
    echo "2. Reset Database"
    echo "3. Pump Test Data (Populate UI)"
    echo "4. Exit"
    echo "=========================================="
    echo -n "Enter your choice (1-4): "
}

while true; do
    show_menu
    read choice
    case $choice in
        1)
            chmod +x scripts/run.sh
            ./scripts/run.sh
            exit 0
            ;;
        2)
            chmod +x scripts/reset-db.sh
            ./scripts/reset-db.sh
            read -p "Press enter to continue..."
            ;;
        3)
            chmod +x scripts/pump-data.sh
            ./scripts/pump-data.sh
            read -p "Press enter to continue..."
            ;;
        4)
            exit 0
            ;;
        *)
            echo "Invalid option"
            sleep 1
            ;;
    esac
done
