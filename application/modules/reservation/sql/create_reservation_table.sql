-- Room Reservation Module Database Schema
-- Create table for room reservations

CREATE TABLE IF NOT EXISTS `tb_mas_room_reservations` (
  `intReservationID` int(11) NOT NULL AUTO_INCREMENT,
  `intRoomID` int(11) NOT NULL,
  `intFacultyID` int(11) NOT NULL,
  `strPurpose` varchar(255) NOT NULL,
  `dteReservationDate` date NOT NULL,
  `dteStartTime` time NOT NULL,
  `dteEndTime` time NOT NULL,
  `enumStatus` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `strRemarks` text,
  `dteCreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `intApprovedBy` int(11) DEFAULT NULL,
  `dteApproved` datetime DEFAULT NULL,
  `intCreatedBy` int(11) NOT NULL,
  `dteUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`intReservationID`),
  KEY `idx_room_date` (`intRoomID`, `dteReservationDate`),
  KEY `idx_faculty` (`intFacultyID`),
  KEY `idx_status` (`enumStatus`),
  KEY `idx_date_time` (`dteReservationDate`, `dteStartTime`, `dteEndTime`),
  CONSTRAINT `fk_reservation_room` FOREIGN KEY (`intRoomID`) REFERENCES `tb_mas_classrooms` (`intID`) ON DELETE CASCADE,
  CONSTRAINT `fk_reservation_faculty` FOREIGN KEY (`intFacultyID`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE CASCADE,
  CONSTRAINT `fk_reservation_approver` FOREIGN KEY (`intApprovedBy`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE SET NULL,
  CONSTRAINT `fk_reservation_creator` FOREIGN KEY (`intCreatedBy`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Insert sample data for testing
INSERT INTO `tb_mas_room_reservations` 
(`intRoomID`, `intFacultyID`, `strPurpose`, `dteReservationDate`, `dteStartTime`, `dteEndTime`, `enumStatus`, `strRemarks`, `intCreatedBy`) 
VALUES 
(1, 1, 'Faculty Meeting', '2024-01-15', '09:00:00', '11:00:00', 'approved', 'Monthly department meeting', 1),
(2, 2, 'Student Consultation', '2024-01-16', '14:00:00', '16:00:00', 'pending', 'Individual student consultations', 2),
(1, 3, 'Workshop Training', '2024-01-17', '08:00:00', '12:00:00', 'pending', 'Technology workshop for faculty', 3);
