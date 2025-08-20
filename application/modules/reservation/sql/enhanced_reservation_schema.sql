-- Enhanced Room Reservation Module Database Schema
-- Extensions for Recurring Reservations, Calendar Integration, and Equipment Booking

-- 1. Add recurring reservation fields to existing table
ALTER TABLE `tb_mas_room_reservations` 
ADD COLUMN `enumRecurrenceType` enum('none','daily','weekly','monthly') NOT NULL DEFAULT 'none' AFTER `enumStatus`,
ADD COLUMN `intRecurrenceInterval` int(2) DEFAULT 1 AFTER `enumRecurrenceType`,
ADD COLUMN `strRecurrenceDays` varchar(20) DEFAULT NULL AFTER `intRecurrenceInterval`,
ADD COLUMN `dteRecurrenceEnd` date DEFAULT NULL AFTER `strRecurrenceDays`,
ADD COLUMN `intParentReservationID` int(11) DEFAULT NULL AFTER `dteRecurrenceEnd`,
ADD COLUMN `intMaxCapacity` int(3) DEFAULT NULL AFTER `intParentReservationID`,
ADD COLUMN `intSetupTime` int(3) DEFAULT 0 AFTER `intMaxCapacity`,
ADD COLUMN `intCleanupTime` int(3) DEFAULT 0 AFTER `intSetupTime`,
ADD COLUMN `enumPriority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal' AFTER `intCleanupTime`,
ADD COLUMN `strContactInfo` varchar(255) DEFAULT NULL AFTER `enumPriority`,
ADD COLUMN `boolRequiresApproval` tinyint(1) NOT NULL DEFAULT 1 AFTER `strContactInfo`;

-- Add indexes for new fields
ALTER TABLE `tb_mas_room_reservations`
ADD KEY `idx_recurrence_type` (`enumRecurrenceType`),
ADD KEY `idx_parent_reservation` (`intParentReservationID`),
ADD KEY `idx_priority` (`enumPriority`),
ADD KEY `idx_recurrence_end` (`dteRecurrenceEnd`);

-- Add foreign key for parent reservation (for recurring series)
ALTER TABLE `tb_mas_room_reservations`
ADD CONSTRAINT `fk_parent_reservation` FOREIGN KEY (`intParentReservationID`) REFERENCES `tb_mas_room_reservations` (`intReservationID`) ON DELETE CASCADE;

-- 2. Create equipment/resources table
CREATE TABLE IF NOT EXISTS `tb_mas_room_equipment` (
  `intEquipmentID` int(11) NOT NULL AUTO_INCREMENT,
  `strEquipmentCode` varchar(50) NOT NULL,
  `strEquipmentName` varchar(255) NOT NULL,
  `strDescription` text,
  `enumType` enum('av','furniture','technology','catering','other') NOT NULL DEFAULT 'other',
  `intQuantityAvailable` int(3) NOT NULL DEFAULT 1,
  `boolRequiresSetup` tinyint(1) NOT NULL DEFAULT 0,
  `intSetupTimeMinutes` int(3) DEFAULT 0,
  `strLocation` varchar(255) DEFAULT NULL,
  `enumStatus` enum('available','maintenance','retired') NOT NULL DEFAULT 'available',
  `decCostPerHour` decimal(10,2) DEFAULT 0.00,
  `strNotes` text,
  `dteCreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dteUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `intCreatedBy` int(11) NOT NULL,
  PRIMARY KEY (`intEquipmentID`),
  UNIQUE KEY `uk_equipment_code` (`strEquipmentCode`),
  KEY `idx_equipment_type` (`enumType`),
  KEY `idx_equipment_status` (`enumStatus`),
  CONSTRAINT `fk_equipment_creator` FOREIGN KEY (`intCreatedBy`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 3. Create reservation-equipment junction table
CREATE TABLE IF NOT EXISTS `tb_mas_reservation_equipment` (
  `intReservationEquipmentID` int(11) NOT NULL AUTO_INCREMENT,
  `intReservationID` int(11) NOT NULL,
  `intEquipmentID` int(11) NOT NULL,
  `intQuantityRequested` int(3) NOT NULL DEFAULT 1,
  `intQuantityApproved` int(3) DEFAULT NULL,
  `enumStatus` enum('requested','approved','denied','delivered','returned') NOT NULL DEFAULT 'requested',
  `strNotes` text,
  `dteCreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dteUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`intReservationEquipmentID`),
  UNIQUE KEY `uk_reservation_equipment` (`intReservationID`, `intEquipmentID`),
  KEY `idx_equipment_status` (`enumStatus`),
  CONSTRAINT `fk_res_equip_reservation` FOREIGN KEY (`intReservationID`) REFERENCES `tb_mas_room_reservations` (`intReservationID`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_equip_equipment` FOREIGN KEY (`intEquipmentID`) REFERENCES `tb_mas_room_equipment` (`intEquipmentID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 4. Create room capacity table (for different room configurations)
CREATE TABLE IF NOT EXISTS `tb_mas_room_configurations` (
  `intConfigurationID` int(11) NOT NULL AUTO_INCREMENT,
  `intRoomID` int(11) NOT NULL,
  `strConfigurationName` varchar(100) NOT NULL,
  `intMaxCapacity` int(3) NOT NULL,
  `strDescription` text,
  `boolIsDefault` tinyint(1) NOT NULL DEFAULT 0,
  `enumStatus` enum('active','inactive') NOT NULL DEFAULT 'active',
  `dteCreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dteUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`intConfigurationID`),
  KEY `idx_room_config` (`intRoomID`),
  KEY `idx_config_status` (`enumStatus`),
  CONSTRAINT `fk_config_room` FOREIGN KEY (`intRoomID`) REFERENCES `tb_mas_classrooms` (`intID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 5. Create calendar integration settings table
CREATE TABLE IF NOT EXISTS `tb_mas_calendar_settings` (
  `intSettingID` int(11) NOT NULL AUTO_INCREMENT,
  `intFacultyID` int(11) NOT NULL,
  `strCalendarProvider` enum('google','outlook','ical') NOT NULL,
  `strCalendarToken` text,
  `strCalendarID` varchar(255),
  `boolAutoSync` tinyint(1) NOT NULL DEFAULT 0,
  `boolSyncReservations` tinyint(1) NOT NULL DEFAULT 1,
  `boolSyncClasses` tinyint(1) NOT NULL DEFAULT 1,
  `intSyncInterval` int(3) NOT NULL DEFAULT 60,
  `dteLastSync` datetime DEFAULT NULL,
  `enumStatus` enum('active','inactive','error') NOT NULL DEFAULT 'active',
  `dteCreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dteUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`intSettingID`),
  UNIQUE KEY `uk_faculty_provider` (`intFacultyID`, `strCalendarProvider`),
  KEY `idx_calendar_status` (`enumStatus`),
  CONSTRAINT `fk_calendar_faculty` FOREIGN KEY (`intFacultyID`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 6. Insert sample equipment data
INSERT INTO `tb_mas_room_equipment` 
(`strEquipmentCode`, `strEquipmentName`, `strDescription`, `enumType`, `intQuantityAvailable`, `boolRequiresSetup`, `intSetupTimeMinutes`, `strLocation`, `intCreatedBy`) 
VALUES 
('PROJ-001', 'Digital Projector', 'HD Digital Projector with HDMI/VGA inputs', 'av', 5, 1, 15, 'AV Storage Room', 1),
('LAPTOP-001', 'Presentation Laptop', 'Windows 10 Laptop for presentations', 'technology', 3, 0, 5, 'IT Department', 1),
('SCREEN-001', 'Projection Screen', 'Portable projection screen 100 inch', 'av', 8, 1, 10, 'AV Storage Room', 1),
('MIC-001', 'Wireless Microphone', 'Wireless handheld microphone system', 'av', 4, 1, 10, 'AV Storage Room', 1),
('CHAIR-001', 'Additional Chairs', 'Stackable conference chairs', 'furniture', 50, 0, 0, 'Storage Room B', 1),
('TABLE-001', 'Folding Tables', 'Rectangular folding tables', 'furniture', 20, 0, 5, 'Storage Room B', 1),
('COFFEE-001', 'Coffee Service', 'Coffee and refreshment service', 'catering', 1, 1, 30, 'Catering Department', 1),
('WHITEBOARD-001', 'Portable Whiteboard', 'Mobile whiteboard with markers', 'furniture', 6, 0, 2, 'Supply Room', 1);

-- 7. Insert sample room configurations
INSERT INTO `tb_mas_room_configurations` 
(`intRoomID`, `strConfigurationName`, `intMaxCapacity`, `strDescription`, `boolIsDefault`) 
VALUES 
(1, 'Standard Classroom', 30, 'Standard classroom setup with rows of desks', 1),
(1, 'Conference Style', 20, 'Conference table setup for meetings', 0),
(1, 'Workshop Style', 25, 'Tables arranged for group work and activities', 0),
(2, 'Lecture Hall', 50, 'Tiered seating for large lectures', 1),
(2, 'Presentation Mode', 40, 'Flat floor setup for presentations', 0);

-- 8. Create indexes for performance optimization
CREATE INDEX idx_reservation_date_time ON tb_mas_room_reservations(dteReservationDate, dteStartTime, dteEndTime);
CREATE INDEX idx_reservation_faculty_date ON tb_mas_room_reservations(intFacultyID, dteReservationDate);
CREATE INDEX idx_equipment_type_status ON tb_mas_room_equipment(enumType, enumStatus);
CREATE INDEX idx_reservation_equipment_status ON tb_mas_reservation_equipment(enumStatus, dteCreated);

-- 9. Create views for common queries
CREATE OR REPLACE VIEW vw_reservation_details AS
SELECT 
    r.*,
    c.strRoomCode,
    c.strDescription as strRoomDescription,
    c.intCapacity as intRoomCapacity,
    f.strFirstname,
    f.strLastname,
    f.strEmail,
    a.strFirstname as strApproverFirstname,
    a.strLastname as strApproverLastname,
    CASE 
        WHEN r.enumRecurrenceType != 'none' THEN 'Yes'
        ELSE 'No'
    END as strIsRecurring,
    CASE 
        WHEN r.intParentReservationID IS NOT NULL THEN 'Yes'
        ELSE 'No'
    END as strIsRecurrenceInstance
FROM tb_mas_room_reservations r
LEFT JOIN tb_mas_classrooms c ON r.intRoomID = c.intID
LEFT JOIN tb_mas_faculty f ON r.intFacultyID = f.intID
LEFT JOIN tb_mas_faculty a ON r.intApprovedBy = a.intID;

CREATE OR REPLACE VIEW vw_equipment_availability AS
SELECT 
    e.*,
    COALESCE(SUM(re.intQuantityApproved), 0) as intCurrentlyReserved,
    (e.intQuantityAvailable - COALESCE(SUM(re.intQuantityApproved), 0)) as intAvailableQuantity
FROM tb_mas_room_equipment e
LEFT JOIN tb_mas_reservation_equipment re ON e.intEquipmentID = re.intEquipmentID 
    AND re.enumStatus IN ('approved', 'delivered')
WHERE e.enumStatus = 'available'
GROUP BY e.intEquipmentID;

-- 10. Create stored procedures for recurring reservations
DELIMITER //

CREATE PROCEDURE sp_CreateRecurringReservations(
    IN p_reservation_id INT,
    IN p_recurrence_type ENUM('daily','weekly','monthly'),
    IN p_interval_count INT,
    IN p_recurrence_days VARCHAR(20),
    IN p_end_date DATE
)
BEGIN
    DECLARE v_current_date DATE;
    DECLARE v_original_date DATE;
    DECLARE v_room_id INT;
    DECLARE v_faculty_id INT;
    DECLARE v_purpose VARCHAR(255);
    DECLARE v_start_time TIME;
    DECLARE v_end_time TIME;
    DECLARE v_max_capacity INT;
    DECLARE v_setup_time INT;
    DECLARE v_cleanup_time INT;
    DECLARE v_priority ENUM('low','normal','high','urgent');
    DECLARE v_contact_info VARCHAR(255);
    DECLARE v_description TEXT;
    DECLARE v_created_by INT;
    DECLARE v_counter INT DEFAULT 0;
    DECLARE v_max_iterations INT DEFAULT 100; -- Safety limit
    
    -- Get original reservation details
    SELECT dteReservationDate, intRoomID, intFacultyID, strPurpose, dteStartTime, dteEndTime,
           intMaxCapacity, intSetupTime, intCleanupTime, enumPriority, strContactInfo, 
           strDescription, intCreatedBy
    INTO v_original_date, v_room_id, v_faculty_id, v_purpose, v_start_time, v_end_time,
         v_max_capacity, v_setup_time, v_cleanup_time, v_priority, v_contact_info,
         v_description, v_created_by
    FROM tb_mas_room_reservations 
    WHERE intReservationID = p_reservation_id;
    
    SET v_current_date = v_original_date;
    
    -- Create recurring instances
    WHILE v_current_date <= p_end_date AND v_counter < v_max_iterations DO
        -- Calculate next occurrence based on recurrence type
        IF p_recurrence_type = 'daily' THEN
            SET v_current_date = DATE_ADD(v_current_date, INTERVAL p_interval_count DAY);
        ELSEIF p_recurrence_type = 'weekly' THEN
            SET v_current_date = DATE_ADD(v_current_date, INTERVAL p_interval_count WEEK);
        ELSEIF p_recurrence_type = 'monthly' THEN
            SET v_current_date = DATE_ADD(v_current_date, INTERVAL p_interval_count MONTH);
        END IF;
        
        -- Check if we should create reservation for this date
        IF v_current_date <= p_end_date THEN
            -- For weekly recurrence, check if day matches
            IF p_recurrence_type != 'weekly' OR 
               p_recurrence_days IS NULL OR 
               FIND_IN_SET(DAYOFWEEK(v_current_date), p_recurrence_days) > 0 THEN
                
                -- Insert new reservation instance
                INSERT INTO tb_mas_room_reservations (
                    intRoomID, intFacultyID, strPurpose, dteReservationDate, 
                    dteStartTime, dteEndTime, enumStatus, strDescription,
                    enumRecurrenceType, intRecurrenceInterval, strRecurrenceDays,
                    dteRecurrenceEnd, intParentReservationID, intMaxCapacity,
                    intSetupTime, intCleanupTime, enumPriority, strContactInfo,
                    boolRequiresApproval, intCreatedBy, dteCreated
                ) VALUES (
                    v_room_id, v_faculty_id, v_purpose, v_current_date,
                    v_start_time, v_end_time, 'pending', v_description,
                    'none', 1, NULL, NULL, p_reservation_id, v_max_capacity,
                    v_setup_time, v_cleanup_time, v_priority, v_contact_info,
                    1, v_created_by, NOW()
                );
            END IF;
        END IF;
        
        SET v_counter = v_counter + 1;
    END WHILE;
END //

DELIMITER ;

-- 11. Create function to check equipment availability
DELIMITER //

CREATE FUNCTION fn_CheckEquipmentAvailability(
    p_equipment_id INT,
    p_quantity_needed INT,
    p_reservation_date DATE,
    p_start_time TIME,
    p_end_time TIME,
    p_exclude_reservation_id INT
) RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_available_quantity INT DEFAULT 0;
    DECLARE v_conflicting_quantity INT DEFAULT 0;
    
    -- Get total available quantity
    SELECT intQuantityAvailable INTO v_available_quantity
    FROM tb_mas_room_equipment 
    WHERE intEquipmentID = p_equipment_id AND enumStatus = 'available';
    
    -- Get quantity already reserved for the same time slot
    SELECT COALESCE(SUM(re.intQuantityApproved), 0) INTO v_conflicting_quantity
    FROM tb_mas_reservation_equipment re
    JOIN tb_mas_room_reservations r ON re.intReservationID = r.intReservationID
    WHERE re.intEquipmentID = p_equipment_id
      AND re.enumStatus IN ('approved', 'delivered')
      AND r.dteReservationDate = p_reservation_date
      AND r.enumStatus IN ('approved', 'pending')
      AND (
          (r.dteStartTime <= p_start_time AND r.dteEndTime > p_start_time) OR
          (r.dteStartTime < p_end_time AND r.dteEndTime >= p_end_time) OR
          (r.dteStartTime >= p_start_time AND r.dteEndTime <= p_end_time)
      )
      AND (p_exclude_reservation_id IS NULL OR r.intReservationID != p_exclude_reservation_id);
    
    -- Return true if enough quantity is available
    RETURN (v_available_quantity - v_conflicting_quantity) >= p_quantity_needed;
END //

DELIMITER ;
