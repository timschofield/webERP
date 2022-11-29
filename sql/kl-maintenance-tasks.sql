
CREATE TABLE `klmaintenancetasks` (
  `counterindex` int(20) NOT NULL,
  `loccode` varchar(5) NOT NULL,
  `maintenancetype` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `creationuser` varchar(20) NOT NULL,
  `creationdate` date NOT NULL,
  `creationdescription` text NOT NULL,
  `closinguser` varchar(20) NOT NULL,
  `closingdate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `klmaintenancetypes`
--

CREATE TABLE `klmaintenancetypes` (
  `maintenancetype` varchar(20) NOT NULL COMMENT 'code for the type',
  `description` varchar(50) NOT NULL COMMENT 'text description'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `klmaintenancetasks`
--
ALTER TABLE `klmaintenancetasks`
  ADD UNIQUE KEY `Location` (`loccode`,`creationdate`,`counterindex`) USING BTREE;

--
-- Indexes for table `klmaintenancetypes`
--
ALTER TABLE `klmaintenancetypes`
  ADD UNIQUE KEY `maintenancetype` (`maintenancetype`);
COMMIT;
