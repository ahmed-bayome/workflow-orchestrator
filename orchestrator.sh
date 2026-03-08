#!/bin/bash

show_menu() {
    clear
    echo "=========================================="
    echo "   Dynamic Workflow Orchestrator Manager"
    echo "=========================================="
    echo "1. Setup (First time install)"
    echo "2. Run Application"
    echo "3. Reset Database"
    echo "4. Pump Test Data (Populate UI)"
    echo "5. Exit"
    echo "=========================================="
    echo -n "Enter your choice (1-5): "
}

while true; do
    show_menu
    read choice
    case $choice in
        1)
            chmod +x scripts/setup.sh 2>/dev/null
            ./scripts/setup.sh || bash scripts/setup.sh
            read -p "Press enter to continue..."
            ;;
        2)
            chmod +x scripts/run.sh
            ./scripts/run.sh
            exit 0
            ;;
        3)
            chmod +x scripts/reset-db.sh
            ./scripts/reset-db.sh
            read -p "Press enter to continue..."
            ;;
        4)
            chmod +x scripts/pump-data.sh
            ./scripts/pump-data.sh
            read -p "Press enter to continue..."
            ;;
        5)
            exit 0
            ;;
        *)
            echo "Invalid option"
            sleep 1
            ;;
    esac
done
