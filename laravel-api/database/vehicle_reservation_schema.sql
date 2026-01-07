CREATE TABLE IF NOT EXISTS `tb_mas_vehicles` (
  `intVehicleID` int(11) NOT NULL AUTO_INCREMENT,
  `strPlateNumber` varchar(50) NOT NULL,
  `strVehicleName` varchar(255) NOT NULL,
  `strModel` varchar(100) DEFAULT NULL,
  `strBrand` varchar(100) DEFAULT NULL,
  `intYear` int(4) DEFAULT NULL,
  `enumType` enum('sedan','suv','van','bus','motorcycle','truck','other') NOT NULL DEFAULT 'sedan',
  `intCapacity` int(3) NOT NULL DEFAULT 4,
  `strColor` varchar(50) DEFAULT NULL,
  `enumFuelType` enum('gasoline','diesel','electric','hybrid') DEFAULT 'gasoline',
  `enumStatus` enum('available','maintenance','retired','reserved') NOT NULL DEFAULT 'available',
  `strLocation` varchar(255) DEFAULT NULL,
  `decCostPerDay` decimal(10,2) DEFAULT 0.00,
  `strNotes` text,
  `dteCreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dteUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `intCreatedBy` int(11) NOT NULL,
  PRIMARY KEY (`intVehicleID`),
  UNIQUE KEY `uk_plate_number` (`strPlateNumber`),
  KEY `idx_vehicle_type` (`enumType`),
  KEY `idx_vehicle_status` (`enumStatus`),
  CONSTRAINT `fk_vehicle_creator` FOREIGN KEY (`intCreatedBy`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_mas_vehicle_reservations` (
  `intVehicleReservationID` int(11) NOT NULL AUTO_INCREMENT,
  `intVehicleID` int(11) NOT NULL,
  `intFacultyID` int(11) NOT NULL,
  `strPurpose` varchar(500) NOT NULL,
  `strDestination` varchar(255) DEFAULT NULL,
  `dteReservationDate` date NOT NULL,
  `dteStartTime` time NOT NULL,
  `dteEndTime` time NOT NULL,
  `dteReturnDate` date DEFAULT NULL,
  `intDriverID` int(11) DEFAULT NULL,
  `strDriverName` varchar(255) DEFAULT NULL,
  `strDriverLicense` varchar(100) DEFAULT NULL,
  `strContactNumber` varchar(50) DEFAULT NULL,
  `intPassengerCount` int(3) DEFAULT 1,
  `enumStatus` enum('pending','approved','rejected','cancelled','completed','ongoing') NOT NULL DEFAULT 'pending',
  `strRemarks` text,
  `dteCreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `intApprovedBy` int(11) DEFAULT NULL,
  `dteApproved` datetime DEFAULT NULL,
  `intCreatedBy` int(11) NOT NULL,
  `dteUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`intVehicleReservationID`),
  KEY `idx_vehicle_date` (`intVehicleID`, `dteReservationDate`),
  KEY `idx_reservation_status` (`enumStatus`),
  KEY `idx_reservation_date` (`dteReservationDate`),
  KEY `idx_faculty_reservations` (`intFacultyID`, `dteReservationDate`),
  CONSTRAINT `fk_vehicle_reservation_vehicle` FOREIGN KEY (`intVehicleID`) REFERENCES `tb_mas_vehicles` (`intVehicleID`) ON DELETE CASCADE,
  CONSTRAINT `fk_vehicle_reservation_faculty` FOREIGN KEY (`intFacultyID`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE CASCADE,
  CONSTRAINT `fk_vehicle_reservation_driver` FOREIGN KEY (`intDriverID`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE SET NULL,
  CONSTRAINT `fk_vehicle_reservation_approver` FOREIGN KEY (`intApprovedBy`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE SET NULL,
  CONSTRAINT `fk_vehicle_reservation_creator` FOREIGN KEY (`intCreatedBy`) REFERENCES `tb_mas_faculty` (`intID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE INDEX idx_vehicle_date_time ON tb_mas_vehicle_reservations(dteReservationDate, dteStartTime, dteEndTime);

INSERT INTO `tb_mas_vehicles` 
(`strPlateNumber`, `strVehicleName`, `strModel`, `strBrand`, `intYear`, `enumType`, `intCapacity`, `strColor`, `enumFuelType`, `enumStatus`, `strLocation`, `intCreatedBy`) 
VALUES 
('ABC-1234', 'Service Van', 'Hiace Commuter', 'Toyota', 2022, 'van', 14, 'White', 'diesel', 'available', 'Main Campus Parking', 1),
('XYZ-5678', 'Admin Sedan', 'Camry', 'Toyota', 2021, 'sedan', 5, 'Silver', 'gasoline', 'available', 'Main Campus Parking', 1),
('DEF-9012', 'Campus Bus', 'Coaster', 'Toyota', 2020, 'bus', 29, 'Blue', 'diesel', 'available', 'Main Campus Parking', 1),
('GHI-3456', 'Utility Pickup', 'Hilux', 'Toyota', 2023, 'truck', 5, 'Black', 'diesel', 'available', 'Main Campus Parking', 1);
