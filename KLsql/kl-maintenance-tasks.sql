
CREATE TABLE `klmaintenancetasks` (
  `counterindex` int(20) NOT NULL AUTO_INCREMENT,
  `loccode` varchar(5) NOT NULL,
  `maintenancetype` varchar(10) NOT NULL,
  `description` text,
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `creationuser` varchar(20) DEFAULT NULL,
  `creationdate` datetime NOT NULL,
  `updateuser` varchar(20) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL,
  `closeuser` varchar(20) DEFAULT NULL,
  `closedate` datetime DEFAULT NULL, 
  UNIQUE `CounterIndex` (`counterindex`)
) ENGINE=InnoDB AUTO_INCREMENT=0  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `klmaintenancetypes`
--

CREATE TABLE `klmaintenancetypes` (
  `maintenancetype` varchar(10) NOT NULL COMMENT 'code for the type',
  `description` varchar(50) NOT NULL COMMENT 'text description'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `klmaintenancetypes`
--

INSERT INTO `klmaintenancetypes` (`maintenancetype`, `description`) VALUES
('AC', 'AC rusak (not regular maintenance)'),
('BOCOR', 'Bocoran air'),
('FURNITURE', 'Furniture rusak'),
('LAMPU', 'Balon lampu mati'),
('LISTRIK', 'Listrik mati atau masalah listrik lain2'),
('PAINT', 'Cat, paint'),
('PINTUKACA', 'Pintu kaca'),
('TOILET', 'Toilet rusak, mentetes, tersumbat, dll'),
('WALLPAPER', 'Wallpaper rusak, lepas, kotor, dll'),
('Z_DLL', 'z_Dan Lain Lain');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `klmaintenancetasks`
--
ALTER TABLE `klmaintenancetasks`
   ADD UNIQUE KEY `Location` (`loccode`,`creationdate`,`counterindex`) USING BTREE,
  ADD UNIQUE KEY `closed` (`closed`,`loccode`,`counterindex`);

--
-- Indexes for table `klmaintenancetypes`
--
ALTER TABLE `klmaintenancetypes`
  ADD UNIQUE KEY `maintenancetype` (`maintenancetype`);
COMMIT;

UPDATE www_users SET  modulesallowed = "1,0,0,0,1,0,0,1,0,0,0,0," 
WHERE fullaccess = 17;