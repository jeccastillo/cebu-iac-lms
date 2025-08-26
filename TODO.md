# Fix vm.toggleUserMenu() Issue

## Problem
The user dropdown menu appears but disappears immediately when trying to click menu items due to `ng-mouseleave` triggering when mouse moves from button to dropdown.

## Plan
- [x] Identify the issue (mouseleave behavior)
- [x] Update header.controller.js with better event handling
- [x] Update header.html to fix mouseleave behavior
- [ ] Test the dropdown functionality

## Files to Edit
- frontend/unity-spa/shared/components/header/header.controller.js
- frontend/unity-spa/shared/components/header/header.html

## Progress
- [x] Analysis complete
- [x] Controller updates - Added $timeout service and delay mechanism
- [x] Template updates - Replaced immediate mouseleave with scheduled close
- [ ] Testing

## Changes Made

### Controller (header.controller.js)
- Added `$timeout` dependency injection
- Added `closeMenuTimeout` variable to track pending timeouts
- Enhanced `toggleUserMenu()` to cancel pending timeouts
- Enhanced `closeUserMenu()` to properly clean up timeouts
- Added `scheduleCloseUserMenu()` with 300ms delay
- Added `cancelCloseUserMenu()` to prevent premature closing

### Template (header.html)
- Replaced `ng-mouseleave="vm.closeUserMenu()"` with `ng-mouseleave="vm.scheduleCloseUserMenu()"`
- Added `ng-mouseenter="vm.cancelCloseUserMenu()"` to both container and dropdown
- Added same mouse events to dropdown menu itself to prevent closing when hovering over menu items

## How It Works
1. When mouse leaves the button/container area, it schedules a close after 300ms
2. If mouse enters the dropdown menu within that time, the close is cancelled
3. This allows smooth movement from button to menu items without closing
4. Menu still closes when mouse leaves the entire dropdown area
