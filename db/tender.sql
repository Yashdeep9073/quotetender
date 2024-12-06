-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 02, 2024 at 09:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tender`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `history` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `mobile` varchar(100) NOT NULL,
  `Staff_Email` varchar(200) NOT NULL,
  `activation_token` varchar(300) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`, `email`, `status`, `history`, `type`, `mobile`, `Staff_Email`, `activation_token`, `id`) VALUES
('DVEPL', '202cb962ac59075b964b07152d234b70', 'dvepl@yahoo.in', '1', '09-16-2023 ', '', '9417601244', '', '0', 3),
('Iqbal', '827ccb0eea8a706c4c34a16891f84e7b', 'office@dvepl.com', '1', '11-08-2023 ', '', '9257217609', '', 'b7cd5411a4ae361485b0c25d898f678e', 9),
('Arun', '827ccb0eea8a706c4c34a16891f84e7b', 'arun@dvepl.com', '1', '11-09-2023 ', '', '9464100344', '', '808e6e1b0dfd5e5fab9bb8f2a56c8acf', 11),
('Anuradha', 'dcddb75469b4b4875094e14561e573d8', 'sales@dvepl.com', '1', '06-13-2024 ', '', '9464100344', '', '75f29bd0268bed4c88277604d7fc528c', 16),
('KAMAL', 'fc28ef4d06b0f1c1314fbde2daaf8a5a', 'info_dvepl@yahoo.in', '1', '07-09-2024 ', '', '9988675003', '', '1fe11f614bf5239bb5f0d56ae41ae578', 17);

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `admin_id` varchar(11) DEFAULT NULL,
  `navigation_menu_id` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `admin_id`, `navigation_menu_id`) VALUES
(5, '5', '10'),
(6, '5', '11'),
(7, '5', '14'),
(8, '5', '20'),
(9, '5', '9'),
(10, '3', '1'),
(11, '4', '1'),
(12, '4', '2'),
(13, '4', '3'),
(14, '4', '4'),
(15, '4', '6'),
(16, '4', '17'),
(17, '4', '18'),
(18, '4', '19'),
(19, '4', '20'),
(20, '4', '21'),
(21, '7', '1'),
(26, '7', '3'),
(27, '7', '11'),
(28, '7', '22'),
(29, '3', '2'),
(30, '3', '5'),
(31, '3', '6'),
(32, '3', '15'),
(34, '8', '18'),
(35, '8', '19'),
(36, '8', '20'),
(37, '9', '18'),
(38, '9', '19'),
(41, '10', '18'),
(42, '10', '19'),
(43, '10', '20'),
(44, '10', '21'),
(46, '11', '31'),
(47, '11', '18'),
(48, '11', '19'),
(52, '10', '30'),
(54, '14', '6'),
(56, '6', '10'),
(57, '11', '20'),
(58, '10', '16'),
(59, '15', '5'),
(60, '16', '18'),
(61, '16', '19'),
(62, '16', '20'),
(63, '9', '2'),
(64, '9', '3'),
(65, '9', '4'),
(66, '9', '5'),
(67, '9', '12'),
(68, '9', '13'),
(69, '9', '20'),
(70, '17', '18'),
(71, '11', '30'),
(72, '18', '20'),
(73, '3', '4');

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE `banner` (
  `banner_id` int(11) NOT NULL,
  `banner_link` varchar(100) NOT NULL,
  `banner_images` varchar(100) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banner`
--

INSERT INTO `banner` (`banner_id`, `banner_link`, `banner_images`, `status`) VALUES
(3, 'rwrwrwr', '654c8126813ec_64f5f9223de8c_advt-2.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `brand`
--

CREATE TABLE `brand` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `brand_image` varchar(100) NOT NULL,
  `status` int(100) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brand`
--

INSERT INTO `brand` (`brand_id`, `brand_name`, `brand_image`, `status`) VALUES
(37, 'L&T', '6554ae9cac63e_7.jpg', 1),
(38, 'Schneider', '6554aeafc9691_1.jpg', 1),
(39, 'Havells', '6554aebf117b2_2.jpg', 1),
(40, 'HPL', '6554aef514f63_4.jpg', 1),
(41, 'C&S', '6554af0600061_6.jpg', 1),
(42, 'Legrand', '6554af15a47d8_3.jpg', 1),
(43, 'Indoasian', '6554af289a1a8_8.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `Category_Name` varchar(100) NOT NULL,
  `parent_category` varchar(100) NOT NULL,
  `show_in_menu` varchar(100) NOT NULL,
  `show_popular_list` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `Category_Name`, `parent_category`, `show_in_menu`, `show_popular_list`, `image`, `status`) VALUES
(29, 'Havells', 'Havells', 'yes', 'yes', '65586563d6ebb_Untitled-1.jpg', '1'),
(30, 'L&T', 'L & T', 'yes', 'yes', '65587bfae1a43_Untitled-6.jpg', '1'),
(31, 'Schneider', 'Havells', 'yes', 'yes', '65587c1674a58_Untitled-8.jpg', '1'),
(32, 'Legrand', 'Legrand', 'yes', 'yes', '65587c3f14f46_Untitled-2.jpg', '1'),
(33, 'Indoasian', 'Indoasian', 'yes', 'yes', '65587cf92a9db_Untitled-7.jpg', '1');

-- --------------------------------------------------------

--
-- Table structure for table `ct_user`
--

CREATE TABLE `ct_user` (
  `sno` int(11) NOT NULL,
  `name` int(100) NOT NULL,
  `address` int(100) NOT NULL,
  `status` int(100) NOT NULL,
  `alloted` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`, `status`) VALUES
(37, 'I & PH Himachal Pardesh', 1),
(38, 'CPWD Jammu', 1),
(39, 'BSF', 1),
(40, 'Jammu & Kashmir PHE', 1),
(41, 'MES', 1),
(42, 'CPWD ', 1),
(43, 'AAI Airport Authorities of India', 1),
(44, 'PHE', 1),
(51, 'Railway', 1),
(52, 'NHPC', 1),
(53, 'FCI', 1),
(54, 'SKIMS', 1),
(55, 'PWD', 1),
(56, 'Private', 1),
(57, 'Others', 1);

-- --------------------------------------------------------

--
-- Table structure for table `division`
--

CREATE TABLE `division` (
  `division_id` int(11) NOT NULL,
  `division_name` varchar(255) NOT NULL,
  `section_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `division`
--

INSERT INTO `division` (`division_id`, `division_name`, `section_id`, `status`) VALUES
(15, '133 Works Engineer', 51, 1),
(16, '134 Works Engineer', 51, 1),
(17, 'CWE (AF) Jammu', 52, 1),
(18, 'CWE (AF) Leh', 52, 1),
(19, 'CWE (AF) Srinagar', 52, 1),
(20, 'CWE Kumbathang', 53, 1),
(21, 'GE (I) Project No 1 Leh', 53, 1),
(22, 'HQ 138 Works ENGR', 53, 1),
(23, '135 Works Engineer', 54, 1),
(24, 'CWE Dhar Road', 54, 1),
(27, 'CWE Udhampur', 54, 1),
(29, 'CWE (AF) Ambala', 55, 1),
(30, 'CWE (AF) Bhisiana', 55, 1),
(31, 'CWE (AF) Bikaner', 55, 1),
(32, 'CWE (AF) Chandigarh', 55, 1),
(33, 'CWE (AF) Gurgaon', 55, 1),
(35, 'CWE Ambala', 56, 1),
(36, 'CWE Chandimandir', 56, 1),
(37, 'CWE Patiala', 56, 1),
(38, 'CWE Shimla Hills', 56, 1),
(39, 'CWE Delhi Cantt', 57, 1),
(40, 'CWE New Delhi', 57, 1),
(41, 'CWE (P) Delhi Cantt', 57, 1),
(42, 'CWE (U) Delhi Cantt', 57, 1),
(43, 'CWE Amritsar', 58, 1),
(44, 'CWE Ferozepur', 58, 1),
(45, 'CWE Jalandhar', 58, 1),
(46, 'CWE Jammu', 59, 1),
(47, 'CWE Mamun', 59, 1),
(48, 'CWE Pathankot', 59, 1),
(49, 'CWE Yol', 59, 1),
(50, 'AGE (I) (R&D) Haldwani', 60, 1),
(51, 'AGE (I) (R&D) Manali', 60, 1),
(52, 'AGE (I) (R&D) Delhi', 60, 1),
(53, 'GE (I) (R&D) Chandigarh', 60, 1),
(54, 'GE (I) (R&D) Chandipur', 60, 1),
(55, 'GE (I) (R&D) Dehradun', 60, 1),
(56, 'CWE Nagrota', 54, 1),
(57, 'CWE Rajouri', 54, 1),
(58, 'GE I 873 EWS', 54, 1),
(59, 'CWE Bikaner', 61, 1),
(60, 'CWE Bathinda', 61, 1),
(61, 'CWE Ganganagar', 61, 1),
(62, 'CWE (AF) (North) Bangalore', 62, 1),
(63, 'CWE (AF) Secunderabad', 62, 1),
(64, 'CWE (AF) (South) Bangalore', 62, 1),
(65, 'CWE (AF) Trivandrum', 62, 1),
(66, 'CWE Dinjan', 63, 1),
(67, 'CWE HQ 137 WE', 63, 1),
(68, 'CWE Shillong', 63, 1),
(69, 'CWE No. 2 Port Blair', 64, 1),
(70, 'CWE Port Blair', 64, 1),
(71, 'GE (I) 866 EWS', 64, 1),
(72, 'GE (I) Campbell Bay', 64, 1),
(73, 'AGE (I) CG Jakhau', 65, 1),
(74, 'AGE (I) CG Noida', 65, 1),
(75, 'GE (CG) Kochi', 65, 1),
(76, 'GE (CG) Porbandar', 65, 1),
(77, 'GE Daman', 65, 1),
(78, 'GE (I) CG Goa', 65, 1),
(79, 'Hydraulic Circle Doda', 66, 1),
(80, 'Hydraulic Circle Jammu', 66, 1),
(81, 'Hydraulic Circle Kathua', 66, 1),
(82, 'Hydraulic Circle Poonch', 66, 1),
(83, 'Hydraulic Circle Rajouri', 66, 1),
(84, 'Hydraulic Circle Udhampur', 66, 1),
(85, 'Mech Rural Circle Jammu', 66, 1),
(86, 'Mech Urban Circle Jammu', 66, 1),
(87, 'Hydraulic Circle Anantnag', 67, 1),
(88, 'Hydraulic Circle Baramulla', 67, 1),
(89, 'Hydraulic Circle Budgam', 67, 1),
(90, 'Hydraulic Circle Pulwama', 67, 1),
(91, 'Mech Circle North Srinagar', 67, 1),
(92, 'Mech Circle South Srinagar', 67, 1),
(93, 'CWE Bareilly', 68, 1),
(94, 'CWE Dehradun', 68, 1),
(95, 'CWE Hills Dehradun', 68, 1),
(96, 'CWE Hills Pithoragarh', 68, 1),
(97, 'CWE Meerut', 68, 1),
(98, 'CWE No 2 Meerut', 68, 1),
(99, 'CWE Bengdubi', 69, 1),
(100, 'CWE Binnaguri', 69, 1),
(101, 'CWE Tenga', 69, 1),
(102, 'CWE Tezpur', 69, 1),
(103, 'HQ 136 Works Engineers', 69, 1),
(104, 'GE (I) (CG and P) Kolkata', 70, 1),
(105, 'GE (I) (CG) Bhubaneshwar', 70, 1),
(106, 'GE (I) (CG) Chennai', 70, 1),
(107, 'AGE (I) RND AVADI', 71, 1),
(108, 'AGE (I) RND KOCHI', 71, 1),
(109, 'GE (I) RND (E) BANGALORE', 71, 1),
(110, 'GE (I) RND GIRINAGAR', 71, 1),
(111, 'GE (I) RND KANCHANBAGH', 71, 1),
(112, 'GE (I) RND PASHAN', 71, 1),
(113, 'GE (I) RND (W) BANGALORE', 71, 1),
(114, 'CWE (AF) BHUJ', 72, 1),
(115, 'CWE (AF) CHILODA', 72, 1),
(116, 'CWE (AF) Jaisalmer', 72, 1),
(117, 'CWE (AF) JAMNAGAR', 72, 1),
(118, 'CWE (AF) JODHPUR', 72, 1),
(119, 'CWE (AF) LOHOGAON', 72, 1),
(122, 'CWE HISAR', 76, 1);

-- --------------------------------------------------------

--
-- Table structure for table `google_captcha`
--

CREATE TABLE `google_captcha` (
  `captcha_id` int(11) NOT NULL,
  `site_key` varchar(100) NOT NULL,
  `secret_key` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `google_captcha`
--

INSERT INTO `google_captcha` (`captcha_id`, `site_key`, `secret_key`) VALUES
(1, '6LeyShEqAAAAAJIMoyXfN7DmfesxwLNYOgBHIh4N', '6LeyShEqAAAAAKVRQAie1sCk9E5rBjvR9Ce0x5k_');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `firm_name` varchar(100) NOT NULL,
  `mobile` varchar(100) NOT NULL,
  `email_id` varchar(100) NOT NULL,
  `city_state` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created_date` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT '0',
  `activation_token` varchar(100) NOT NULL,
  `expiry_time` varchar(100) NOT NULL,
  `max_request` varchar(100) NOT NULL,
  `pending_request` varchar(11) DEFAULT `max_request`
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `name`, `firm_name`, `mobile`, `email_id`, `city_state`, `password`, `created_date`, `status`, `activation_token`, `expiry_time`, `max_request`, `pending_request`) VALUES
(39, 'Abhimanyu Mahajan', 'Dinesh Enterprises', '9988454420', 'abhimanyu.twentyone@gmail.com', '', '', '2023-11-20 07:22:40 AM', '0', '', '', '', ''),
(40, 'MAHESH KUMAR', 'MAHESH KUMAR SINGLA CONTRACTOR', '9646024000', 'mahesh.k.singla@gmail.com', '', '', '2023-11-20 07:33:44 AM', '0', '', '', '', ''),
(43, 'RAKESH', 'RHG Constructions Private Limited', '9814042824', 'rhgconstructionspvtltd@gmail.com', '', '', '2023-11-21 04:51:36 AM', '0', '', '', '', ''),
(51, 'GURWINDER SINGH', 'BHARAT ENGINEERS', '9478969961', 'bharatengineers@ymail.com', 'jalandhar', '', '2023-11-22 09:10:39 AM', '1', '', '', '100', '100'),
(52, 'PAWAN', 'BANSI BUILDER AND CONTRACTORS PVT LTD', '9417009929', 'bansibuilder@yahoo.co.in', '', '', '2023-11-23 05:26:12 AM', '0', '', '', '', ''),
(55, 'RANJEET SINGH', 'SANGA SINGH AND CO', '9515308061', 'sangasinghandco@gmail.com', '', '', '2023-12-04 05:17:41 AM', '0', '', '', '', ''),
(56, 'Kashish Gupta', 'A K ENGINEERS & CO.', '9086677776', 'kashishgupta9525@gmail.com', '', '', '2023-12-07 08:30:59 AM', '0', '', '', '', ''),
(57, 'Sourabh', 'Vardhman Airport Solutions Pvt Ltd', '9997076589', 'sourabh.vardhmanairport@gmail.com', '', '', '2023-12-09 06:07:06 AM', '0', '', '', '', ''),
(59, 'Om Parkash', 'Om Parkash Nargotra Constructions Pvt Ltd', '9419251789', 'opncmail@gmail.com', '', '', '2023-12-11 07:05:42 AM', '0', '', '', '', ''),
(60, 'PARSHOTAM KUMAR GUPTA', 'PARSHOTAM KUMAR GUPTA', '9419115035', 'mohinmalhotra07@gmail.com', '', '', '2023-12-11 08:22:07 AM', '0', '', '', '', ''),
(61, 'Mr. Ahuja', 'J.K & Company', '9914406468', 'ahujaint123@gmail.com', '', '', '2023-12-12 05:59:18 AM', '0', '', '', '', ''),
(62, 'CHIRAG', 'JCC ENGINEERS PRIVATE LIMITED', '9216021126', 'chirag@jccengineers.com', '', '', '2023-12-27 06:45:26 AM', '0', '', '', '', ''),
(63, 'Tarsem Lal Saini', 'TARSAM LAL SAINI CONTRACTOR', '9803004622', 'rudraconstructions82@gmail.com', '', '', '2023-12-27 06:52:40 AM', '0', '', '', '', ''),
(64, 'Kapil Gupta', 'Vijay Kumar Gupta and Co', '7889873300', 'kapil25gupta@gmail.com', '', '', '2023-12-27 07:12:12 AM', '0', '', '', '', ''),
(66, 'SANGA SINGH', 'SANGA SINGH AND CO', '9914699982', 'kularandco@gmail.com', '', '', '2024-01-01 06:24:13 AM', '0', '', '', '', ''),
(67, 'Rajesh Sharma', 'Vijay Power Generator', '8920407682', 'vijaypowerdelhi@gmail.com', 'Delhi', 'c80ca71dac0599a0193ae71b3b1cf287', '2024-01-02 14:13:27 PM', '1', '0', '', '50', '49'),
(68, 'DARSHAN GANDI', 'Darshan Kumar Gandhi', '9419352785', 'Kiranelectricworks@gmail.com', '', '', '2024-01-02 09:52:45 AM', '0', '', '', '', ''),
(69, 'Rajnesh Duggal', 'DUGGAL CONSTRUCTION CO.', '8360703504', 'duggal6375@gmail.com', '', '', '2024-01-02 10:01:10 AM', '0', '', '', '', ''),
(70, 'Vikas Jindal', 'M/s Jindal & Company', '9592000367', 'vjindal.co@gmail.com', 'Ludhiana', 'b6b124310b9995c61e4dc84c1db623af', '2024-01-05 14:05:21 PM', '1', '0', '', '50', '50'),
(71, 'Vijay Soni', 'Des Raj AND SONS BUILDERS PVT LTD', '7888571575', 'info@desrajgroup.com', 'Chandigarh', 'ac9b59a1091bd85b6afbd384f130d99b', '2024-01-05 14:45:05 PM', '1', '0', '', '50', '50'),
(72, 'Naresh', 'N K BUILDERS', '9419160051', 'nk723builders@gmail.com', '', '', '2024-01-05 09:45:17 AM', '0', '', '', '', ''),
(73, 'Sunny', 'Kailu Constructions Co.', '9855268845', 'kailuconstruction99@gmail.com', 'Pathankot', 'd7d73e311767758f993fdb6883c7196b', '2024-01-05 16:52:10 PM', '1', '0', '', '50', '39'),
(74, 'PARMODH KUMAR', 'RAMAN ELECTRIC TRADING CO', '9416020195', 'retc40@gmail.com', 'ambala cantt', '2703246cf88e6a271b8b1e4844059040', '2024-01-06 11:04:32 AM', '1', '0', '', '50', '50'),
(76, 'Arun Gupta', 'Electromechanics Engineers & Contractors', '9419188228', 'arunenterprisespvtltd@rediffmail.com', '', '', '2024-01-06 05:52:18 AM', '0', '', '', '', ''),
(77, 'ANIL RAIZADA', 'B D Raizada Projects Pvt Ltd', '9810013159', 'bdraizada@gmail.com', '', '', '2024-01-06 05:55:47 AM', '0', '', '', '', ''),
(78, 'Mahesh Tanwar', 'Mayur Enterprises', '9810267015', 'mayurenterprises63@gmail.com', '', '', '2024-01-06 05:59:11 AM', '0', '', '', '', ''),
(79, 'Dushant Dhiman', 'L&T electrical & automation', '9958205298', 'dushant.dhiman@lntebg.com', 'Pathankot', '1cfaa49295c42106e88cab28baf41100', '2024-01-06 17:28:39 PM', '1', '0', '', '50', '46'),
(80, 'Dinesh Aggarwal', 'KULBHUSHAN AGGARWAL', '7986659046', 'aggarwal.dinesh57@gmail.com', '', '', '2024-01-08 08:28:02 AM', '0', '', '', '', ''),
(81, 'Moolraj Ramesh Chander', 'Moolraj Ramesh Chander', '9419913142', 'rameahchander13142@gmail.com', 'Jammu', '3e0101ecf0d8427cf14f3f6dc2f0282d', '2024-01-08 15:41:48 PM', '1', '0', '', '20', '20'),
(82, '   janmitha bangera', 'company', '78866678999', 'janmithabangera@gmail.com', 'Dakshina Kannada', '202cb962ac59075b964b07152d234b70', '2024-01-13 16:36:47 PM', '1', 'b6e161c0b8ee1f9b2e5d2a6ec4f03bef', '', '66', '61'),
(83, 'IQBAL', 'DVEPL', '9257217609', 'office@dvepl.com', 'pathankot', '827ccb0eea8a706c4c34a16891f84e7b', '2024-01-22 11:31:37 AM', '1', '0', '', '20', '1'),
(84, '   Dinesh Chowdhary', 'DVEPL', '9417601244', 'dinesh@dvepl.com', 'Pathankot', 'dcddb75469b4b4875094e14561e573d8', '2024-01-23 03:45:24 AM', '1', '91ca1918f2ed33d73cd3932a234f869c', '', '100', '94'),
(92, 'ROCKY', 'R D BANSAL', '9814039555', 'aspl_007@yahoo.co.in', '', '', '2024-06-13 09:47:39 AM', '0', '', '', '', ''),
(94, 'Pawan', 'Bansi builder and contractor pvt ltd ', '941709929', 'bansibuilder@yahoo.co.in', '', '', '2024-06-13 10:47:36 AM', '0', '', '', '', ''),
(97, 'M/S Mohd Maqbool rather', '', '', '', '', '', '2024-06-13 11:29:36 AM', '0', '', '', '', ''),
(98, 'M/S Nancy Enterprises', '', '', '', '', '', '2024-06-13 11:40:04 AM', '0', '', '', '', ''),
(99, 'Qazi sons', '', '', '', '', '', '2024-06-13 12:01:33 PM', '0', '', '', '', ''),
(100, '', 'Bharat multitech industries pvt.ltd', '', '', '', '', '2024-06-13 12:12:13 PM', '0', '', '', '', ''),
(101, '', 'Northern India Constructions Co', '', '', '', '', '2024-06-13 12:19:26 PM', '0', '', '', '', ''),
(102, '', 'Rm infrastructure', '', '', '', '', '2024-06-13 12:42:42 PM', '0', '', '', '', ''),
(103, '', 'harsh constructions co', '', '', '', '', '2024-06-13 12:57:32 PM', '0', '', '', '', ''),
(104, '', 'AR Constructions', '', '', '', '', '2024-06-14 03:46:59 AM', '0', '', '', '', ''),
(105, '________', 'J.K COMPANY', '________', '________', '', '', '2024-06-14 03:56:51 AM', '0', '', '', '', ''),
(106, '', 'J.D BUILDERS', '', '', '', '', '2024-06-14 06:07:06 AM', '0', '', '', '', ''),
(107, 'PARSHOTAM KUMAR GUPTA', '', '', '', '', '', '2024-06-14 06:12:12 AM', '0', '', '', '', ''),
(108, 'MR. NAVJOT SINGH', 'SHRI RAM BUILDERS', '9417679900', 'SKARM.SK@GMAIL.COM', '', '', '2024-06-14 06:36:32 AM', '0', '', '', '', ''),
(109, 'PARVINDER BHARDWAJ', 'NOT KNOWN', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 07:10:24 AM', '0', '', '', '', ''),
(110, 'NOT KNOWN', 'JAI ENGINEERS & CONTRACTORS', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 07:15:24 AM', '0', '', '', '', ''),
(111, 'ROCKEY AGGERWAL', 'RD BANSAL', '9814039555', 'aspl007@yahoo.co.in', '', '', '2024-06-14 07:25:14 AM', '0', '', '', '', ''),
(112, 'NOT KNOWN', 'SRI SAI CONSTRUCTION COMPANY	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 07:29:46 AM', '0', '', '', '', ''),
(113, 'NOT KNOWN', 'M/S GDR CONSTRUCTIONS	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 08:11:37 AM', '0', '', '', '', ''),
(114, 'NOT KNOWN', 'R B TRADERS	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 08:15:15 AM', '0', '', '', '', ''),
(115, 'NOT KNOWN', 'narayan construction	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 08:18:17 AM', '0', '', '', '', ''),
(116, 'NOT KNOWN', 'HARISH SALES CORPORATION	', '', '', '', '', '2024-06-14 08:24:22 AM', '0', '', '', '', ''),
(117, 'NOT KNOWN', 'VIJAY JINDAL BUILDERS PVT LTD	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 08:29:07 AM', '0', '', '', '', ''),
(118, 'NOT KNOWN', 'M/s Shakti Electric Works	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 08:35:34 AM', '0', '', '', '', ''),
(119, 'NOT KNOWN', 'JR CONSTRSTRUCTION CO.	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 08:40:26 AM', '0', '', '', '', ''),
(120, 'NOT KNOWN', 'Havish Construction	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 08:59:47 AM', '0', '', '', '', ''),
(121, 'NOT KNOWN', 'HEENA CONSTRUCTION CO	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 09:05:37 AM', '0', '', '', '', ''),
(122, 'NOT KNOWN', 'D R CONTRACTORS	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 09:09:13 AM', '0', '', '', '', ''),
(123, 'NOT KNOWN', 'M/S JATINDERA ENTERPRISES	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 09:17:38 AM', '0', '', '', '', ''),
(124, 'NOT KNOWN', 'D R STEEL CONSTRUCTION COMPANY PRIVATE LIMITED	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 09:45:48 AM', '0', '', '', '', ''),
(125, 'NOT KNOWN', 'VIJAY JINDAL BUILDERS PVT LTD	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 09:55:07 AM', '0', '', '', '', ''),
(126, 'NOT KNOWN', 'RAJENDER ENTERPRISES	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 10:02:04 AM', '0', '', '', '', ''),
(127, 'NOT KNOWN', 'VERMA BROTHERS	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 10:10:53 AM', '0', '', '', '', ''),
(128, 'NOT KNOWN', 'Mathra Dass Ahuja and Sons', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 10:14:02 AM', '0', '', '', '', ''),
(129, 'NOT KNOWN', 'PADMA ENTERPRISES	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 10:18:41 AM', '0', '', '', '', ''),
(130, 'NOT KNOWN', 'Aman Enterprises	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 10:23:29 AM', '0', '', '', '', ''),
(131, 'NOT KNOWN', 'ARCHON POWERINFRA INDIA PRIVATE LIMITED	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 10:28:45 AM', '0', '', '', '', ''),
(132, 'NOT KNOWN', 'ARR CONSTRUCTIONS	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 10:30:56 AM', '0', '', '', '', ''),
(133, 'NOT KNOWN', 'M/S NINDI BUILDERS	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 10:36:30 AM', '0', '', '', '', ''),
(134, 'NOT KNOWN', 'M/s VARINDER PAL SINGH AND CO	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 10:40:10 AM', '0', '', '', '', ''),
(135, 'MR.BHAT MUSAIB', 'M M SHAWL Engineers and contractors pvt ltd	', '1942477506', 'mmsencpltd@yahoo.com', '', '', '2024-06-14 10:48:33 AM', '0', '', '', '', ''),
(136, 'not known', 'raj engineers', '9000000000', 'test@gmail.com', '', '', '2024-06-14 10:53:40 AM', '0', '', '', '', ''),
(137, 'Not known', 'M/s BURU ENTERPRISES	', '9000000000', 'test@gmail.com', '', '', '2024-06-14 10:58:27 AM', '0', '', '', '', ''),
(138, 'not known', 'M/s Sheetal Associates	', '9000000000', 'test@gmail.com', '', '', '2024-06-14 11:03:12 AM', '0', '', '', '', ''),
(139, 'not known', 'SUMEET TRADING CORPORATION	', '9000000000', 'test@gmail.com', '', '', '2024-06-14 11:11:33 AM', '0', '', '', '', ''),
(140, 'not known', 'v k engineers and builders	', '9000000000', 'test@gmail.com', '', '', '2024-06-14 11:50:37 AM', '0', '', '', '', ''),
(141, 'not known', '	GURBUX SINGH AND CO', '9000000000', 'test@gmail.com', '', '', '2024-06-14 11:53:30 AM', '0', '', '', '', ''),
(142, 'NOT KNOWN', 'Jai Ambey Construction Co.	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 12:13:39 PM', '0', '', '', '', ''),
(143, 'GURJEET', '	Gurjit const co', '7888769272', 'gurjit.const@gmail.com', '', '', '2024-06-14 12:16:44 PM', '0', '', '', '', ''),
(144, 'NOT KNOWN', 'M/S NANCY ENTERPRISES	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 12:19:06 PM', '0', '', '', '', ''),
(145, 'KULDEEP SINGH', 'NOT KNOWN', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 12:21:46 PM', '0', '', '', '', ''),
(146, 'NOT KNOWN', 'DES RAJ AND SONS BUILDERS PVT LTD	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-14 12:24:25 PM', '0', '', '', '', ''),
(150, 'ROCKEY AGGERWAL', 'R D Bansal	', '9814039555', 'aspl007@yahoo.co.in', '', '', '2024-06-15 03:45:43 AM', '0', '', '', '', ''),
(151, 'MR. NAVJOT SINGH', 'SHRI RAM BUILDER', '9417679900', 'SKARM.SK@GMAIL.COM', '', '', '2024-06-15 04:28:48 AM', '0', '', '', '', ''),
(152, 'NOT KNOWN', 'NAFREF ENGINEERS PVT LTD	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-15 04:32:48 AM', '0', '', '', '', ''),
(153, 'NOT KNOWN', 'M/s godara infratech and power industries	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-15 04:35:15 AM', '0', '', '', '', ''),
(154, 'NOT KNOWN', 'RATTAN CHAND CHANDAN AND CO	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-15 04:38:41 AM', '0', '', '', '', ''),
(155, 'NOT KNOWN', 'VEE ESS ENGINEERS	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-15 05:20:03 AM', '0', '', '', '', ''),
(156, 'Jiwan Singh	', 'JIWAN SINGH', '9858052090', 'TEST@GMAIL.COM', '', '', '2024-06-15 05:22:08 AM', '0', '', '', '', ''),
(157, 'ABHIMANYU', 'dinesh enterprises	', '9988454420', 'TEST@GMAIL.COM', '', '', '2024-06-15 05:26:41 AM', '0', '', '', '', ''),
(158, 'SUNNY', 'kailu construction co	', '9855268845', 'KAILUCONSTRUCTIONS99@GMAIL.COM', '', '', '2024-06-15 05:28:49 AM', '0', '', '', '', ''),
(159, 'NOT KNOWN', 'M/S PARSHOTAM KUMAR GUPTA	', '9419115035', 'TEST@GMAIL.COM', '', '', '2024-06-15 05:31:56 AM', '0', '', '', '', ''),
(160, '', 'DES RAJ AND SONS BUILDERS PVT LTD', '9815975300', 'info@desrajgroup.com', '', '', '2024-06-19 09:21:16 AM', '0', '', '', '', ''),
(161, '', 'SHEETAL ASSOCIATES', '9418088069', 'sheetal.parmar2@yahoo.com', '', '', '2024-06-19 09:24:02 AM', '0', '', '', '', ''),
(162, '', 'RB TRADERS', '9417018971', 'RB_TRADERS.99@REDIFFMAIL.COM', '', '', '2024-06-19 09:31:10 AM', '0', '', '', '', ''),
(163, '', 'VEE ESS ENGINEERS', '9888570129', ' rajinderrehan@gmail.com', '', '', '2024-06-19 09:36:04 AM', '0', '', '', '', ''),
(164, '', 'SUMEET TRADING CORPORATION', '9419706553/9419178701', '', '', '', '2024-06-19 09:40:43 AM', '0', '', '', '', ''),
(165, 'NOT KNOWN', 'GURBUX SINGH AND CO.', '9213451770', '	GURBUXSINGH2019@gmail.com', '', '', '2024-06-19 09:44:15 AM', '0', '', '', '', ''),
(166, 'NOT KNOWN', 'NARAYAN CONSTRUCTIONS', '9419115035', 'TEST@GMAIL.COM', '', '', '2024-06-19 09:59:36 AM', '0', '', '', '', ''),
(167, 'NOT KNOWN', 'VEE ESS ENGINEERS', '9888570129', 'rajinderrehan@gmail.com', '', '', '2024-06-19 10:05:14 AM', '0', '', '', '', ''),
(168, 'NOT KNOWN', 'v k engineers and builders', '9780024372', 'TEST@GMAIL.COM', '', '', '2024-06-19 10:09:16 AM', '0', '', '', '', ''),
(169, 'RAJBIR SINGH', 'RB TRADERS', '9417018971', 'rb_traders.99@rediffmail.com', '', '', '2024-06-20 08:09:20 AM', '0', '', '', '', ''),
(170, 'NOT KNOWN', 'SATPAL & BROTHERS', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-21 03:49:37 AM', '0', '', '', '', ''),
(171, 'NOT KNOWN', 'M/s Northern India Construction co	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-21 04:16:18 AM', '0', '', '', '', ''),
(172, 'NOT KNOWN', 'M/S V K TRADERS	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-21 04:19:27 AM', '0', '', '', '', ''),
(173, 'NOT KNOWN', 'M/s R V Enterprises	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-21 04:21:36 AM', '0', '', '', '', ''),
(174, 'NOT KNOWN', 'M/S SINGH AND ASSOCIATES	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-21 04:24:11 AM', '0', '', '', '', ''),
(175, 'NOT KNOWN', 'Om Parkash Nargotra Constructions Pvt Ltd	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-21 04:31:03 AM', '0', '', '', '', ''),
(176, 'NOT KNOWN', 'M/S VED PAUL MAHAJAN	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-21 04:38:45 AM', '0', '', '', '', ''),
(177, 'NOT KNOWN', 'Elite Builders', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-22 11:22:03 AM', '0', '', '', '', ''),
(178, 'NOT KNOWN', 'M/S RAJ ENGINEERS', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-22 11:25:51 AM', '0', '', '', '', ''),
(179, 'NOT KNOWN', 'M/S RAJ ENGINEERS', '9000000000', 'TETS@GMAIL.COM', '', '', '2024-06-22 11:29:03 AM', '0', '', '', '', ''),
(180, 'NOT KNOWN', 'SUN MANN ENGINEERS', '9000000000', 'TEST@GAMIL.COM', '', '', '2024-06-22 11:41:56 AM', '0', '', '', '', ''),
(181, 'NOT KNOWN', '	INDERPAL SINGH', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-22 11:50:27 AM', '0', '', '', '', ''),
(182, 'RAJNESH DUGGAL', 'DUGGAL CONSTRUCTION CO', '9815152055', 'duggal6375@gmail.com', '', '', '2024-06-24 08:44:35 AM', '0', '', '', '', ''),
(183, 'NOT KNOWN', 'M/s BURU ENTERPRISES	', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-24 09:04:15 AM', '0', '', '', '', ''),
(184, 'NOT KNOWN', 'narayan construction', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-24 10:24:30 AM', '0', '', '', '', ''),
(185, 'NOT KNOWN', 'KEWAL SONS', '9779299593 / 6280192389', 'Kewal_sons@yahoo.co.in', '', '', '2024-06-24 11:12:29 AM', '0', '', '', '', ''),
(186, 'NOT KNOWN', 'M/S JAI ENGINEERS & CONTRACTORS', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-24 11:15:52 AM', '0', '', '', '', ''),
(187, 'NOT KNOWN', 'Arun enterprises', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-24 11:29:40 AM', '0', '', '', '', ''),
(188, 'ROCKEY AGGERWAL', 'R D Bansal	', '9814039555', 'aspl007@yahoo.co.in', '', '', '2024-06-24 11:53:32 AM', '0', '', '', '', ''),
(189, 'NOT KNOWN', 'B D BATRA AND SONS', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-24 12:01:27 PM', '0', '', '', '', ''),
(190, 'NOT KNOWN', 'Sree Ram Enterprises', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-25 04:08:31 AM', '0', '', '', '', ''),
(191, '', 'Malpotra Enterprises', '8559055182', 'malpotra.enterprises@gmail.com', '', '', '2024-06-25 04:11:56 AM', '0', '', '', '', ''),
(192, 'NOT KNOWN', 'shiv shanker enterprises', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-28 04:58:27 AM', '0', '', '', '', ''),
(193, 'DINESH EH TECH', 'unique power & control system', '8950534164', 'Uniquepower80@gmail.com', '', '', '2024-06-29 07:16:00 AM', '0', '', '', '', ''),
(194, 'NOT KNOWN', 'SRM BUILDERS AND ENGINERS PVT.LIMITTED', '9417224200', 'TEST@GMAIL.COM', '', '', '2024-06-29 07:24:19 AM', '0', '', '', '', ''),
(195, 'NOT KNOWN', 'Sh. Raj Kumar Malik Contractor', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-06-29 07:27:51 AM', '0', '', '', '', ''),
(196, 'Aakanksha Murgai', 'sasfsdgvsfv', '09915292520', 'akinsr05@gmail.com', 'Nawanshahr', 'befe32232c6574ff1af6122404154c6b', '2024-07-01 16:31:32 PM', '0', 'adca76a5a588b5e144eda08a01606baa', '', '', ''),
(197, 'NOT KNOWN', 'shiv shanker enterprises', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-01 12:25:53 PM', '0', '', '', '', ''),
(198, 'NOT KNOWN', 'Bairagi Builders', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-01 12:28:07 PM', '0', '', '', '', ''),
(199, 'SUKHDEV RAM', 'NOT KNOWN', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-01 12:53:45 PM', '0', '', '', '', ''),
(200, 'NOT KNOWN', 'HARPAL ENGINEERS AND BUILDERS', '81462 53252', 'harpalsr@gmail.com', '', '', '2024-07-01 12:58:02 PM', '0', '', '', '', ''),
(201, 'NOT KNOWN', 'BANSI BUILDER AND CONTRACTORS PVT LTD', '9000000000', 'bansibuilder@yahoo.co.in', '', '', '2024-07-02 06:47:58 AM', '0', '', '', '', ''),
(202, 'NOT KNOWN', 'Tarmac Road and Roof Builders Srinagar', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 06:56:08 AM', '0', '', '', '', ''),
(203, 'NOT KNOWN', 'MOHAN LAL AND SONS', '7889964597', 'TEST@GMAIL.COM', '', '', '2024-07-02 07:17:19 AM', '0', '', '', '', ''),
(204, 'NOT KNOWN', 'KEWAL SONS', '9779299593 / 6280192389', 'Kewal_sons@yahoo.co.in', '', '', '2024-07-02 08:55:17 AM', '0', '', '', '', ''),
(205, 'NOT KNOWN', 'DES RAJ NAGPAL CONTRACTORS PVT.LTD', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 08:57:39 AM', '0', '', '', '', ''),
(206, '9000000000', 'Sarbat Construction Co', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 09:01:18 AM', '0', '', '', '', ''),
(207, 'NOT KNOWN', 'JCC ENGINEERS PRIVATE LIMITED', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 09:12:09 AM', '0', '', '', '', ''),
(208, 'NOT KNOWN', 'M/S RAJ ENGINEERS', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 09:17:07 AM', '0', '', '', '', ''),
(209, 'NOT KNOWN', 'GEPS PROJECTS', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 09:19:44 AM', '0', '', '', '', ''),
(210, 'NOT KNOWN', 'MV Engineers And Contractors', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 09:21:51 AM', '0', '', '', '', ''),
(211, 'NOT KNOWN', 'FANGALIA TRADERS', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 09:32:04 AM', '0', '', '', '', ''),
(212, 'NOT KNOWN', 'Rajindera Enterprises', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 09:36:01 AM', '0', '', '', '', ''),
(213, 'NOT KNOWN', 'shiv shanker enterprises', '', '', '', '', '2024-07-02 11:36:18 AM', '0', '', '', '', ''),
(214, 'NOT KNOWN', 'shiv shanker enterprises', '9000000000', 'TEST @GMAIL.COM', '', '', '2024-07-02 11:38:35 AM', '0', '', '', '', ''),
(215, 'NOT KNOWN', 'shiv shanker enterprises', '9000000000', 'TEST @GMAIL.COM', '', '', '2024-07-02 11:38:51 AM', '0', '', '', '', ''),
(216, 'NOT KNOWN', 'shiv shanker enterprises', '9000000000', 'TEST @GMAIL.COM', '', '', '2024-07-02 11:38:54 AM', '0', '', '', '', ''),
(217, 'NOT KNOWN', 'shiv shanker enterprises', '9000000000', 'TEST @GMAIL.COM', '', '', '2024-07-02 11:38:59 AM', '0', '', '', '', ''),
(218, 'NOT KNOWN', 'shiv shanker enterprises', '9000000000', 'TEST @GMAIL.COM', '', '', '2024-07-02 11:39:13 AM', '0', '', '', '', ''),
(219, 'NOT KNOWN', 'shiv shanker enterprises', '9000000000', 'TEST @GMAIL.COM', '', '', '2024-07-02 11:39:24 AM', '0', '', '', '', ''),
(220, 'NOT KNOWN', 'HARISH SALES CORPORATION', '90000000000', 'TEST@GMAIL.COM', '', '', '2024-07-02 11:44:41 AM', '0', '', '', '', ''),
(221, 'Rohit Jasrotia', 'DV ELECTROMATIC PVT LTD', '8872969700', 'rohit@dvepl.com', 'Pathankot', '82e919255a058139c2b71a541a9a1216', '2024-07-02 18:56:59 PM', '1', '0', '', '50', '22'),
(222, 'Dinesh Chowdhary', 'DVEPL', '9417601244', 'dvepl@yahoo.in', 'Pathankot', '827ccb0eea8a706c4c34a16891f84e7b', '2024-07-02 19:18:37 PM', '1', '0', '', '100', '90'),
(223, 'Arun', 'DVEPL', '9464100344', 'arun@dvepl.com', 'pathankot', 'e55b38d2364ee37c17cb2b061f601e7f', '2024-07-03 12:46:23 PM', '1', '0', '', '50', '48'),
(224, 'KamalKant', 'DVEPL', '9464100344', 'info_dvepl@yahoo.in', 'PATHANKOT', '3b119a689fd0d6a1205589b34eb4f7bb', '2024-07-03 17:42:47 PM', '1', '0', '', '25', '24'),
(226, 'Anuradha', 'DVEPL', '9875965212', 'sales@dvepl.com', 'pathankot', 'ae27f0d8c4ad589bcba94aa13a99695d', '2024-07-03 18:21:53 PM', '1', '0', '', '25', '24'),
(227, 'Parshant dua', 'Parkash Electric company ', '9876742510', 'parshant.dua@parkashelectric.com', 'Amritsar', '827ccb0eea8a706c4c34a16891f84e7b', '2024-07-06 17:27:08 PM', '1', '0', '', '50', '50'),
(228, 'NOT KNOWN', 'GULSHAN SINGH GHAI AND SON', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-08 04:10:34 AM', '0', '', '', '', ''),
(229, 'NOT KNOWN', 'slathia construction works', '9000000000', 'TEST@GMAIL.COM', '', '', '2024-07-08 04:28:15 AM', '0', '', '', '', ''),
(230, 'not known', 'MAHESH KUMAR SINGLA CONTRACTOR', '9000000000', 'test@gmail.com', '', '', '2024-07-10 04:07:29 AM', '0', '', '', '', ''),
(231, 'SANJAY', 'DV ELECTROMATIC PVT LTD', '7589301244', 'SANJAY@DVEPL.COM', 'PATHANKOT', 'b588366893fb6ffbf6c8daeef9d24570', '2024-07-16 10:31:19 AM', '1', '0', '0', '25', '24'),
(232, 'not known', 'Mukesh Construction Co.', '9000000000', 'test@gmail.com', '', '', '2024-07-18 04:16:37 AM', '0', '', '', '', ''),
(233, 'not known', 'M/s JAIMAA ASSOCIATES', '9000000000', 'test@gmail.com', '', '', '2024-07-18 04:24:19 AM', '0', '', '', '', ''),
(234, 'RAJINDER', 'VEE ESS ENGINEERS', '9888570129', ' rajinderrehan@gmail.com', '', '', '2024-07-19 06:19:22 AM', '0', '', '', '', ''),
(235, 'RAJINDER', 'VEE ESS ENGINEERS', '9888570129', ' rajinderrehan@gmail.com', '', '', '2024-07-19 06:19:36 AM', '0', '', '', '', ''),
(236, 'RAJINDER', 'VEE ESS ENGINEERS', '9888570129', ' rajinderrehan@gmail.com', '', '', '2024-07-19 06:19:43 AM', '0', '', '', '', ''),
(237, 'NOT KNOWN', 'M/S JIWAN SINGH', '9858052090', 'malkindersingh70@gmail.com', '', '', '2024-07-19 08:50:05 AM', '0', '', '', '', ''),
(238, 'Rakesh', 'VIS22', '8699790303', 'cleaningexpertaus@gmail.com', 'Vuxar', '202cb962ac59075b964b07152d234b70', '2024-07-22 00:22:32 AM', '1', '0', '0', '11', '9'),
(241, 'Raj kumar', 'Sound Eng', '1234567890', 'rajk53080@gmail.com', 'jalandhar', 'e10adc3949ba59abbe56e057f20f883e', '2024-07-22 17:14:28 PM', '1', '619a2381b6479b3030e11d99019bc0a9', '', '140', '85'),
(242, 'rakesh', 'VIS', '9870443528', 'rakeshrai71@gmail.com', 'Jal', '202cb962ac59075b964b07152d234b70', '2024-07-26 19:54:07 PM', '1', '0', '', '111', '110'),
(244, 'Yashdeep', 'dj vfx smw', '1234567890', 'ydeep9073@gmail.com', 'jalandhar', 'e10adc3949ba59abbe56e057f20f883e', '2024-07-31 13:19:53 PM', '1', 'ae5a331f18f25534b9337ebb7a1ad411', '', '123', '117');

-- --------------------------------------------------------

--
-- Table structure for table `navigation_menus`
--

CREATE TABLE `navigation_menus` (
  `id` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `navigation_menus`
--

INSERT INTO `navigation_menus` (`id`, `title`) VALUES
(1, 'All'),
(2, 'Add Section'),
(3, 'Manage Section'),
(4, 'Add Division'),
(5, 'Manage Division'),
(6, 'Sub Division'),
(7, 'Manage Sub Division'),
(8, 'Add Category'),
(9, 'Manage Category'),
(10, 'Add Brands'),
(11, 'Manage Brands'),
(12, 'Add Departments'),
(13, 'Manage Departments'),
(14, 'Add Price List'),
(15, 'Manage Price List'),
(16, 'Add Staff User'),
(17, 'Manage Staff User'),
(18, 'Tender Request'),
(19, 'Sent Tender'),
(20, 'Alot Tender'),
(21, 'Award Tender'),
(22, 'Registered Users'),
(23, 'Add Banner'),
(24, 'Manage Banner'),
(25, 'Add Content'),
(26, 'Manage Content'),
(27, 'Setting'),
(28, 'Email Services'),
(29, 'Logs Report'),
(30, 'Update Tenders'),
(31, 'View Tenders');

-- --------------------------------------------------------

--
-- Table structure for table `price_list`
--

CREATE TABLE `price_list` (
  `price_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `prilce_file` varchar(100) NOT NULL,
  `date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `price_list`
--

INSERT INTO `price_list` (`price_id`, `category`, `title`, `brand_name`, `prilce_file`, `date`) VALUES
(17, 'Havells', 'Switchgears', 'Havells', '655865c08e384_Havells Pricelist 1st March 2023.pdf', '18-Nov-2023');

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `section_name`, `status`) VALUES
(51, 'CE 31 Zone', 1),
(52, 'CE (AF) Udhampur', 1),
(53, 'CE Leh', 1),
(54, 'CE Udhampur', 1),
(55, 'CE (AF) WAC Palam', 1),
(56, 'CE Chandigarh', 1),
(57, 'CE Delhi Zone ', 1),
(58, 'CE Jalandhar Zone', 1),
(59, 'CE Pathankot Zone', 1),
(60, 'ADG (OF and DRDO) and CE (R&D) Delhi', 1),
(61, 'CE Bathinda Zone', 1),
(62, 'CE (AF) Bangalore', 1),
(63, 'CE Shillong Zone', 1),
(64, 'CE (A&N) Zone', 1),
(65, 'ADG (CG & Project) Chennai & CE (CG) Goa', 1),
(66, 'CE PHE Jammu', 1),
(67, 'CE PHE Kashmir', 1),
(68, 'CE Bareilly', 1),
(69, 'CE Siliguri', 1),
(70, 'ADG (Projects) and CE (CG) Visakhapatnam', 1),
(71, 'ADG (OF and DRDO) AND CE (R and D) SECUNDERABAD - MES', 1),
(72, 'CE SWC AND CE (AF) GANDHINAGAR - MES', 1),
(73, 'test', 0),
(74, 'class 1oth', 0),
(76, 'CE JAIPUR', 1),
(77, 'CE JABALPUR', 1),
(78, 'PRIVATE', 1);

-- --------------------------------------------------------

--
-- Table structure for table `smtp_email`
--

CREATE TABLE `smtp_email` (
  `smtp_id` int(11) NOT NULL,
  `from_email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `hosts` varchar(100) NOT NULL,
  `ports` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `smtp_email`
--

INSERT INTO `smtp_email` (`smtp_id`, `from_email`, `password`, `hosts`, `ports`) VALUES
(1, 'info@quotetender.in', '@@Zxcv@123', 'mail.hostinger.com', '465');

-- --------------------------------------------------------

--
-- Table structure for table `sub_division`
--

CREATE TABLE `sub_division` (
  `id` int(5) NOT NULL,
  `subdivision` varchar(255) NOT NULL,
  `division_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_division`
--

INSERT INTO `sub_division` (`id`, `subdivision`, `division_id`, `status`) VALUES
(5, 'GE 874 EWS', 15, 1),
(6, 'GE 969 EWS', 15, 1),
(7, 'GE 861 EWS', 16, 1),
(9, 'GE AF Jammu', 17, 1),
(10, 'GE AF Udhampur', 17, 1),
(11, 'GE (AF) Leh', 18, 1),
(12, 'GE (AF) Thoise', 18, 1),
(13, 'GE (AF) Srinagar', 19, 1),
(14, 'GE (AF) Awantipur', 19, 1),
(17, 'GE Kumbathang', 20, 1),
(18, 'GE (I) Project No 1 Leh', 21, 1),
(19, 'GE 860 EWS', 22, 1),
(20, 'GE 865 EWS', 22, 1),
(21, 'GE Partapur', 22, 1),
(22, 'GE (S) Akhnoor', 23, 1),
(23, 'GE (A) Dhar Road', 24, 1),
(29, 'GE (South) Udhampur', 27, 1),
(37, 'GE (AF) Bhisiana', 30, 1),
(38, 'GE (AF) Nal', 31, 1),
(39, 'GE (AF) Suratgarh', 31, 1),
(40, 'AGE (I) (AF) Amritsar', 32, 1),
(41, 'GE (AF) Adampur', 32, 1),
(42, 'GE (AF) Chandigarh', 32, 1),
(43, 'GE (AF) Faridabad', 33, 1),
(51, 'GE Chandimandir', 36, 1),
(52, 'GE (U) Chandimandir', 36, 1),
(53, 'GE (N) Patiala', 37, 1),
(54, 'GE (S) Patiala', 37, 1),
(55, 'GE (P) Dappar', 37, 1),
(56, 'GE Jutogh', 38, 1),
(57, 'GE Kasauli', 38, 1),
(59, 'GE (East) Delhi Cantt', 39, 1),
(60, 'GE (North) Delhi Cantt', 39, 1),
(61, 'GE E/M Base Hospital Delhi', 40, 1),
(62, 'GE E/M (RR) Hospital Delhi', 40, 1),
(63, 'GE New Delhi', 40, 1),
(64, 'GE (S) New Delhi', 40, 1),
(65, 'CWE (P) Delhi Cantt', 41, 1),
(66, 'GE (U) Electric Supply Delhi Cantt', 42, 1),
(67, 'GE (U) P and M Delhi Cantt', 42, 1),
(68, 'GE (U) Water Supply Delhi Cantt', 42, 1),
(69, 'GE Amritsar', 43, 1),
(70, 'GE Gurdaspur', 43, 1),
(71, 'GE (Nams) Amritsar', 43, 1),
(72, 'GE (East) Ferozepur', 44, 1),
(73, 'GE (West) Ferozepur', 44, 1),
(74, 'AGE (I) Suranussi', 45, 1),
(76, 'GE ENGR Park Jalandhar Cantt', 45, 1),
(77, 'GE (West) Jalandhar Cantt', 45, 1),
(78, 'GE Kapurthla (P)', 45, 1),
(79, 'GE Jammu', 46, 1),
(80, 'GE Kaluchak', 46, 1),
(81, 'GE Satwari', 46, 1),
(82, 'GE (North) Mamun', 47, 1),
(83, 'GE (South) Mamun', 47, 1),
(84, 'GE Basoli', 48, 1),
(85, 'GE (North) Pathankot', 48, 1),
(86, 'GE (South) Pathankot', 48, 1),
(87, 'AGE (I) Dharamshala', 49, 1),
(88, 'GE Dalhousie', 49, 1),
(89, 'GE (KH) Yol', 49, 1),
(90, 'GE Palampur', 49, 1),
(91, 'AGE (I) (R&D) Haldwani', 50, 1),
(92, 'AGE (I) (R&D) Manali', 51, 1),
(93, 'AGE (I) (R&D) Delhi', 52, 1),
(94, 'GE (I) (R&D) Chandigarh', 53, 1),
(95, 'GE (I) (R&D) Chandipur', 54, 1),
(96, 'GE (I) (R&D) Dehradun', 55, 1),
(97, 'AGE (I) Nimu', 20, 1),
(98, 'GE Karbil', 20, 1),
(99, 'GE (North) Udhampur', 27, 1),
(100, 'GE Nagrota', 56, 1),
(101, 'AGE I CIF R', 57, 1),
(102, 'GE 862 EWS', 57, 1),
(103, 'GE 881 EWS', 57, 1),
(104, 'GE (U) Udhampur', 27, 1),
(105, 'GE I 873 EWS', 58, 1),
(106, 'GE (AF) Ambala', 29, 1),
(107, 'GE (AF) Halwara', 29, 1),
(108, 'GE (AF) (P) Halwara', 29, 1),
(109, 'GE (AF) Sarsawa', 29, 1),
(110, 'GE (P)  (AF) Ambala', 29, 1),
(111, 'GE (AF) Gurgaon', 33, 1),
(112, 'GE (P) Bikaner', 59, 1),
(113, 'GE (Army) Suratgarh', 59, 1),
(114, 'GE (North) Bikaner', 59, 1),
(115, 'GE (North) Bathinda', 60, 1),
(116, 'GE (U) Bathinda', 60, 1),
(117, 'GE Abohar', 61, 1),
(118, 'GE Faridkot', 61, 1),
(119, 'GE Lalgarh Jattan', 61, 1),
(122, 'GE(AF) Marathalli', 62, 1),
(123, 'GE(AF) Tambaram', 62, 1),
(124, 'GE AFA Hyderabad', 63, 1),
(125, 'GE (AF) Bidar', 63, 1),
(126, 'GE (AF) Hakimpet Hyberabad', 63, 1),
(127, 'AGE (I) Coimbatore', 64, 1),
(128, 'GE (AF) Sambra', 64, 1),
(129, 'GE (AF) Yalehanka', 64, 1),
(130, 'GE (Maint) (AF) Jalahalli', 64, 1),
(131, 'AGE (I) Suryalanka', 65, 1),
(132, 'GE (AF) Sular', 65, 1),
(133, 'GE (AF) Tanjavur', 65, 1),
(134, 'GE (AF) Trivandrum', 65, 1),
(135, 'AGE (I) Lekhapani', 66, 1),
(136, 'GE Dinjan', 66, 1),
(137, 'GE Jarhat', 66, 1),
(138, 'AGE (I) Agartala', 67, 1),
(139, 'AGE (I) Zakhama', 67, 1),
(140, 'GE 868 EWS', 67, 1),
(141, 'GE 869 EWS', 67, 1),
(142, 'GE 872 EWS', 67, 1),
(143, 'GE Silchar', 67, 1),
(144, 'GE Guwahati', 68, 1),
(145, 'GE Narangi', 68, 1),
(146, 'GE Brichgunj', 69, 1),
(147, 'GE (South) Diglipur', 69, 1),
(148, 'GE Haddo', 70, 1),
(149, 'GE Minnie Bay Port Blair', 70, 1),
(150, 'GE (I) 866 EWS', 71, 1),
(151, 'GE (I) Campbell Bay', 72, 1),
(152, 'GE Shillong', 68, 1),
(153, 'GE Shillong', 68, 1),
(154, 'AGE (I) CG Jakhau', 73, 1),
(155, 'AGE (I) CG Noida', 74, 1),
(156, 'GE (CG) Kochi', 75, 1),
(157, 'GE (CG) Porbandar', 76, 1),
(158, 'GE Daman', 77, 1),
(159, 'GE (I) CG Goa', 78, 1),
(160, 'Division Doda', 79, 1),
(161, 'Division Kishtwar', 79, 1),
(162, 'Division Akhnoor', 80, 1),
(163, 'Division Samba', 80, 1),
(164, 'Rural Division Jammu', 80, 1),
(165, 'Division Kathua', 81, 1),
(166, 'Division Poonch', 82, 1),
(167, 'Division Nowshera', 83, 1),
(168, 'Division Udhampur', 84, 1),
(169, 'Mech Division Kathua', 85, 1),
(170, 'Mech Division Rajouri', 85, 1),
(171, 'Mech Division Udhampur', 85, 1),
(172, 'Mech GWD Division Jammu', 85, 1),
(173, 'Division City-2 Jammu', 86, 1),
(174, 'Mech North Division Jammu', 86, 1),
(175, 'Mech Rural Division Jammu', 86, 1),
(176, 'Mech South Division Jammu', 86, 1),
(177, 'Division Bijibehara', 87, 1),
(178, 'Division Baramulla', 88, 1),
(179, 'Division Sopore', 88, 1),
(180, 'Division Chadoora', 89, 1),
(181, 'Division Awantipora', 90, 1),
(182, 'Division Shopian', 90, 1),
(183, 'Ground Water Division', 91, 1),
(184, 'Mech North Division Sopore', 91, 1),
(185, 'Mech Rural Division Srinagar', 91, 1),
(186, 'Div South Awantipora', 92, 1),
(187, 'Mech Div Srinagar', 92, 1),
(188, 'GE (East) Bareilly', 93, 1),
(189, 'GE (West) Bareilly', 93, 1),
(190, 'GE (P) Dehradun', 94, 1),
(191, 'GE Premnagar', 94, 1),
(192, 'AGE (I) Raiwala', 95, 1),
(193, 'GE 863 EWS', 95, 1),
(194, 'GE Landowne', 95, 1),
(195, 'GE (MES) Clement', 95, 1),
(196, 'GE 871 EWS', 96, 1),
(197, 'GE Pithoragarh', 96, 1),
(198, 'GE Ranikhet', 96, 1),
(199, 'GE (N) Meerut', 97, 1),
(200, 'GE (U) EM Meerut', 97, 1),
(201, 'GE Roorkee', 98, 1),
(202, 'GE (South) Meerut', 98, 1),
(203, 'GE Bengdubi', 99, 1),
(204, 'GE Sukna', 99, 1),
(205, 'GE (N) Binnaguri', 100, 1),
(206, 'GE Sevoke Road', 100, 1),
(207, 'CWE Tenga', 101, 1),
(208, 'GE (N) Tezpur', 102, 1),
(209, 'GE Rangiya', 102, 1),
(210, 'GE (S) Tezpur', 102, 1),
(211, 'GE 867 EWS', 103, 1),
(212, 'GE Gangtok', 103, 1),
(213, 'GE (I) (CG and P) Kolkata', 104, 1),
(214, 'GE (I) (CG) Bhubaneshwar', 105, 1),
(215, 'GE (I) (CG) Chennai', 106, 1),
(216, 'AGE (I) RND AVADI', 107, 1),
(217, 'AGE (I) RND KOCHI', 108, 1),
(218, 'GE (I) RND (E) BANGALORE', 109, 1),
(219, 'GE (I) RND GIRINAGAR', 110, 1),
(220, 'GE (I) RND KANCHANBAGH', 111, 1),
(221, 'GE (I) RND PASHAN', 112, 1),
(222, 'GE (I) RND (W) BANGALORE', 113, 1),
(223, 'GE(AF) BHUJ', 114, 1),
(224, 'GE (AF) JAMNAGAR', 114, 1),
(225, 'GE(AF) NALIYA NO.1', 114, 1),
(226, 'GE (AF) BARODA', 115, 1),
(227, 'GE (AF) CHILODA', 115, 1),
(228, 'GE (AF) Jaisalmer', 116, 1),
(229, 'GE (AF) UTTERLAI', 116, 1),
(230, 'GE (AF) JAMNAGAR NO.2', 117, 1),
(231, 'AGE (1) (AF) JAIPUR', 118, 1),
(232, 'AGE (1) MOUNT ABU', 118, 1),
(233, 'GE (AF) JODHPUR', 118, 1),
(234, 'GE (AF) No. 2 JODHPUR', 118, 1),
(235, 'GE (AF) PHALODI', 118, 1),
(236, 'GE (AF) LOHOGAON ', 119, 1),
(237, 'GE (AF) THANE', 119, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tender`
--

CREATE TABLE `tender` (
  `id` int(11) NOT NULL,
  `tender_id` varchar(100) NOT NULL,
  `user` varchar(100) NOT NULL,
  `firm_name` varchar(100) NOT NULL,
  `mobile` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `add_date` varchar(100) NOT NULL,
  `due_date` varchar(111) NOT NULL,
  `file` varchar(100) NOT NULL,
  `status` varchar(111) NOT NULL DEFAULT '0',
  `tender_no` varchar(100) NOT NULL,
  `reference_code` varchar(100) NOT NULL,
  `work` varchar(100) NOT NULL,
  `section` varchar(100) NOT NULL,
  `sent_date` varchar(100) NOT NULL,
  `alloted` varchar(100) NOT NULL,
  `reminder` varchar(100) NOT NULL,
  `award` int(11) NOT NULL DEFAULT 0,
  `award_date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `user_ip` varchar(100) NOT NULL,
  `login_time` varchar(100) NOT NULL,
  `city` varchar(255) NOT NULL,
  `region` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `username`, `user_ip`, `login_time`, `city`, `region`) VALUES
(1, '29c5de384641c12675c60f8bd152a8ca', 'iqbal', '125.62.127.175', '2023-11-09 12:56:13 PM', '', ''),
(2, 'b6c547b761ecc14fff79484b65c64c95', 'dvepl', '125.62.127.175', '2023-11-09 12:56:37 PM', '', ''),
(3, 'fa4e8d2f9c2232280ebeb91cfeeac960', 'dvepl', '125.62.127.175', '2023-11-09 13:01:36 PM', '', ''),
(4, 'c643fea77f992629a9a8e1c0b902213f', 'dvepl', '125.62.127.175', '2023-11-09 13:12:36 PM', '', ''),
(5, 'b7ca873c8f172462b63bbe93188e40db', 'dvepl', '2409:40d1:101c:ba2:dc5a:2cd2:ce91:6d60', '2023-11-09 13:40:18 PM', '', ''),
(6, '493f24b0ea9ab4347803d87eafd0a2b7', 'dvepl', '2409:40d1:101c:ba2:dc5a:2cd2:ce91:6d60', '2023-11-09 13:42:55 PM', '', ''),
(7, 'cd66533dd9dc54dc0e778f94a60668e0', 'Maithili', '59.89.152.154', '2023-11-09 15:22:27 PM', '', ''),
(8, 'fdd91cf32e95c897bdc194e969d37d92', 'Maithili', '59.89.152.154', '2023-11-09 15:29:04 PM', '', ''),
(9, 'cd4923a74b26cd5c33643b056c41eb63', 'dvepl', '59.89.152.154', '2023-11-09 15:30:39 PM', '', ''),
(10, '9c3c5b48d1f4919b99fc8a227adbf9bd', '', '59.89.152.154', '2023-11-09 15:42:20 PM', '', ''),
(11, '23e672106e710d89ca4df1b6703a25bb', 'dvepl', '59.89.152.154', '2023-11-09 15:45:11 PM', '', ''),
(12, '48dce49f001610ef8ade4c8a0467cd27', 'Arun', '59.89.152.154', '2023-11-09 15:52:56 PM', '', ''),
(13, '059629a0e132db122da9e3070db07a92', 'dvepl', '59.89.152.154', '2023-11-09 15:56:56 PM', '', ''),
(14, '3915f9d7c171640466cd6b40a4a26293', '', '59.89.152.154', '2023-11-09 16:44:25 PM', '', ''),
(15, 'cc3e3459bcb83738e2cd46315cb12ad6', 'Maithili', '59.89.152.154', '2023-11-09 16:44:58 PM', '', ''),
(16, '9728112d4060a917934ad712bc846ef2', 'dvepl', '59.89.152.154', '2023-11-09 16:45:43 PM', '', ''),
(17, '42958326ac8ba7952d63103748f61f47', 'dvepl', '59.89.152.154', '2023-11-09 16:48:16 PM', '', ''),
(18, '3ac99caa8294ca696103ff711c7992b9', 'dvepl', '125.62.127.175', '2023-11-09 19:05:04 PM', '', ''),
(19, 'b8e651f22c3fe494620b28b29291e244', '', '125.62.127.175', '2023-11-09 19:41:56 PM', '', ''),
(20, '0b063328599fd65adf67839d728cc0c0', '', '125.62.127.175', '2023-11-09 19:50:39 PM', '', ''),
(21, 'f8f45727626e45d5f0beaa04d400b1a0', 'dvepl', '125.62.127.175', '2023-11-09 23:06:56 PM', '', ''),
(22, '60d3f0f75442be0a62534d5eb061e935', 'Maithili', '2409:40d1:101c:ba2:1567:e7c:fa79:11bc', '2023-11-10 00:21:33 AM', '', ''),
(23, '6f2a32352af41e1ad50444a00ceae905', 'dvepl', '2409:40d1:101c:ba2:1567:e7c:fa79:11bc', '2023-11-10 00:33:40 AM', '', ''),
(24, '22869411f69e7d91cbcf5237604ad6d0', 'dvepl', '61.0.205.226', '2023-11-10 10:43:40 AM', '', ''),
(25, '01282d87b8e1b7307adb04661b364fec', 'iqbal', '210.89.62.52', '2023-11-10 11:44:34 AM', '', ''),
(26, '236e5a8256b173fa1670a6d53d950e4f', 'dvepl', '59.89.158.155', '2023-11-10 12:12:07 PM', '', ''),
(27, 'd03e4c9b13fe17070c839dfdb4cbf46e', 'dvepl', '59.89.158.155', '2023-11-10 12:14:51 PM', '', ''),
(28, 'fd53a3cc4c5048a7ded8bd999f58a102', 'Maithili', '59.89.158.155', '2023-11-10 12:31:22 PM', '', ''),
(29, '417dc245fe88e60934df9398b8fad420', 'Maithili', '59.89.158.155', '2023-11-10 12:32:02 PM', '', ''),
(30, 'a817669802c33304669a52891b3a1631', 'dvepl', '61.0.200.215', '2023-11-10 12:46:15 PM', '', ''),
(31, '5154366a137f50947bcd648885914877', '', '49.156.106.124', '2023-11-10 13:02:00 PM', '', ''),
(32, '49ce3bb8259c7cfe3c692fdd827c2ca8', 'dvepl', '61.0.200.215', '2023-11-10 13:04:50 PM', '', ''),
(33, 'd9f1bbc9fb8be81c6708a06c7111934d', 'dvepl', '49.156.102.244', '2023-11-11 22:31:44 PM', '', ''),
(34, 'b2c2ab221e2d8e88f96d01465dee4d4d', 'iqbal', '49.156.102.244', '2023-11-11 22:32:02 PM', '', ''),
(35, '5ab09d0b2a046ad374747ebd268af6ca', 'dvepl', '202.14.121.153', '2023-11-12 05:03:18 AM', '', ''),
(36, '8b2c30432c987c48ae231143d4b24ea4', 'dvepl', '202.14.121.153', '2023-11-12 05:04:47 AM', '', ''),
(37, '189ffb07397653a0a555c4e785cf9424', 'dvepl', '49.156.80.188', '2023-11-14 01:08:23 AM', '', ''),
(38, '891c622566cb6a7515071cbbd515f719', 'iqbal', '49.156.80.188', '2023-11-14 01:08:36 AM', '', ''),
(39, '803a76f926c4b21f3a7d1a611ee00cea', 'iqbal', '210.89.62.124', '2023-11-14 10:20:30 AM', '', ''),
(40, 'ba3f36444eb98f5f059401b5f062f3cb', 'arun', '210.89.62.124', '2023-11-14 10:23:37 AM', '', ''),
(41, 'f27890ddc05c595c77f7a709c085c6ac', 'Maithili', '117.203.234.205', '2023-11-14 10:25:12 AM', '', ''),
(42, '25cb4584942d09a2efd6ce76de7e08d1', 'dvepl', '117.203.234.205', '2023-11-14 10:44:58 AM', '', ''),
(43, '18855938dc6fde7a034529ee3a26cc9b', 'iqbal', '210.89.62.124', '2023-11-14 10:47:17 AM', '', ''),
(44, '747a37c70d670442a1901e09973f2e3e', 'Maithili', '117.203.234.205', '2023-11-14 10:49:28 AM', '', ''),
(45, '768c6c0e325b8ea4049d173af1f8ba58', 'dvepl', '117.203.234.205', '2023-11-14 10:51:52 AM', '', ''),
(46, '2340d7ad159910035136611ec92b876f', 'dvepl', '117.203.234.205', '2023-11-14 12:31:46 PM', '', ''),
(47, '4137489da4b153978cae7926197d9a09', 'dvepl', '117.203.234.205', '2023-11-14 12:39:31 PM', '', ''),
(48, '185f7f677adb921cb4266248bfac88e0', '', '117.203.234.205', '2023-11-14 14:26:44 PM', '', ''),
(49, '78f59a26956b25d8b8ab7c93b66ed631', 'Maithili', '61.0.197.198', '2023-11-14 17:03:24 PM', '', ''),
(50, '2a171bffdbfd9dedc39def0ffebbbf77', 'Maithili', '61.0.197.198', '2023-11-14 17:04:22 PM', '', ''),
(51, 'c6f5e4419e2cce18eaae3fa4d7f7e341', 'dvepl', '125.62.124.100', '2023-11-14 19:16:47 PM', '', ''),
(52, '61a1b272f84929c09a34645d9d84daee', 'Arun', '125.62.124.100', '2023-11-15 00:36:54 AM', '', ''),
(53, '17cf2c16c011705c2d1e192eeaaa01ec', 'iqbal', '125.62.124.100', '2023-11-15 00:37:44 AM', '', ''),
(54, '094a1b36870cab38cd7af64f64987fec', 'dvepl', '125.62.124.100', '2023-11-15 00:38:12 AM', '', ''),
(55, '842089cb02baa8ab10646515a07cd4db', 'iqbal', '125.62.124.100', '2023-11-15 00:39:08 AM', '', ''),
(56, 'a47cdc1bed613ce78a1d0ef88cf36547', 'dvepl', '125.62.124.100', '2023-11-15 01:08:11 AM', '', ''),
(57, '389faf2d136bbc4f9341e3328fb42630', 'Arun', '125.62.124.100', '2023-11-15 01:19:20 AM', '', ''),
(58, 'a866188faf27dcf8ac86734376637d9f', 'dvepl', '210.89.62.124', '2023-11-15 10:06:01 AM', '', ''),
(59, '0fc043055f1153c281293006737076a6', 'Maithili', '61.0.197.198', '2023-11-15 10:32:20 AM', '', ''),
(60, 'a97cf62f52e9bf7cacd801e0a7d565de', 'dvepl', '61.0.197.198', '2023-11-15 10:33:00 AM', '', ''),
(61, '09f08a301980a877285301c3c60a4365', 'Maithili', '61.0.197.198', '2023-11-15 10:33:57 AM', '', ''),
(62, 'dcb3c88b434c495c96a7229e3775ad5c', 'dvepl', '61.0.197.198', '2023-11-15 10:35:24 AM', '', ''),
(63, '854a6d7039c7e91374adf0720f1bafdc', 'dvepl', '61.0.197.198', '2023-11-15 11:02:58 AM', '', ''),
(64, '6b13bd9a00c9ec711ad469a255218ff5', 'Maithili', '61.0.197.198', '2023-11-15 11:03:23 AM', '', ''),
(65, 'f53a1246514922ff8769ab113d928868', 'Maithili', '59.89.157.95', '2023-11-15 12:03:54 PM', '', ''),
(66, '520d2e5961a9bd3d0e1d8fee46c0c8c9', 'dvepl', '59.89.157.95', '2023-11-15 12:04:10 PM', '', ''),
(67, '95402cd376c8c8db337764f6dfa7bb60', 'dvepl', '59.89.157.95', '2023-11-15 12:16:31 PM', '', ''),
(68, '092daa7cb1dd27736f9b67cc0245b281', '', '59.89.157.95', '2023-11-15 12:22:20 PM', '', ''),
(69, 'fe9086feeb8a42c048fa51b21f45b7c3', 'Maithili', '59.89.157.95', '2023-11-15 12:23:00 PM', '', ''),
(70, '61f6fe3b2658888113a827b388e5e01d', 'iqbal', '210.89.62.124', '2023-11-15 14:04:08 PM', '', ''),
(71, 'ab55e31d62cdcf0dd9ff90c0f28de69f', 'iqbal', '210.89.62.124', '2023-11-15 14:22:54 PM', '', ''),
(72, '5bcf57b123ab142692bb98068850d2ea', '', '125.62.125.191', '2023-11-15 14:28:08 PM', '', ''),
(73, 'b8ec75f5fe39df05fc6f40f51c37ba5e', 'dvepl', '2409:40d1:100d:1239:283f:c872:7a16:3731', '2023-11-15 14:29:31 PM', '', ''),
(74, 'aadb542e93897dc266572e970bd3f15d', 'dvepl', '2409:40d1:100d:1239:283f:c872:7a16:3731', '2023-11-15 14:30:05 PM', '', ''),
(75, '7a27f5ce8692b0c16fd6acfc1fec8795', 'dvepl', '125.62.125.191', '2023-11-15 14:31:03 PM', '', ''),
(76, 'b2ecd930ab5889c76f561eed093591e4', 'iqbal', '125.62.125.191', '2023-11-15 14:32:04 PM', '', ''),
(77, '0c436030eff48637ee6e78ae08f71e03', 'dvepl', '125.62.125.191', '2023-11-15 14:32:52 PM', '', ''),
(78, '837924355131ce87b5a943551a2bd26d', '', '2409:40d1:100d:1239:283f:c872:7a16:3731', '2023-11-15 14:46:26 PM', '', ''),
(79, 'aebb83cd95d1087e647eca3a8385d6d5', 'dvepl', '2409:40d1:100d:1239:283f:c872:7a16:3731', '2023-11-15 15:11:58 PM', '', ''),
(80, '664be1dded612aca38ddfcf30f022823', 'dvepl', '61.0.19.5', '2023-11-15 16:47:50 PM', '', ''),
(81, '3e2dba02bb4962b285a2144eecff6694', 'dvepl', '61.0.19.5', '2023-11-15 17:19:25 PM', '', ''),
(82, 'd434d4364e8ec20bde3c2f31a9acff61', 'Maithili', '61.0.19.5', '2023-11-15 17:22:23 PM', '', ''),
(83, '39f7a22a90c7458c1925f035440d15fe', 'Maithili', '61.0.19.5', '2023-11-15 17:23:12 PM', '', ''),
(84, '9c7aeac00750bf7a0f95956f29683114', 'dvepl', '125.62.125.191', '2023-11-15 17:31:14 PM', '', ''),
(85, '135e059da1958e6bab4f5f4ec55aa7ef', 'dvepl', '122.173.25.69', '2023-11-16 00:32:50 AM', '', ''),
(86, 'da5d12b55c6e8aa0cf40bb9a79f64355', '', '61.0.19.5', '2023-11-16 09:55:19 AM', '', ''),
(87, 'a3379ab298c021bc442277876a29d33d', 'dvepl', '61.0.19.5', '2023-11-16 09:55:35 AM', '', ''),
(88, '8e165abbd7b680d15cc2903d189f3f16', 'dvepl', '2409:40d1:c:3fee:8000::', '2023-11-16 10:55:13 AM', '', ''),
(89, '7ef5f19d43047afdfa45915bb8e06e75', 'dvepl', '61.0.203.53', '2023-11-16 10:58:27 AM', '', ''),
(90, '830b7bb4e0d4fc704f72cbea183a180b', 'dvepl', '61.0.203.53', '2023-11-16 11:12:18 AM', '', ''),
(91, 'ca9f96d349e177f2f3c149e2528e26cb', 'dvepl', '61.0.205.235', '2023-11-18 12:08:08 PM', '', ''),
(92, '633fb06fa8162c7c6bc9d82e6027a6c5', 'dvepl', '117.210.194.106', '2023-11-18 14:57:55 PM', '', ''),
(93, 'c8d8625afb23deec9f8f1d6b301a760b', 'dvepl', '2401:4900:1c5a:f0d6:19b5:8595:b53e:edd4', '2023-11-18 15:05:08 PM', '', ''),
(94, '5d738f13d9eaf59596a5ec5deb23c0ca', 'dvepl', '49.156.109.175', '2023-11-18 18:17:49 PM', '', ''),
(95, 'b27d2b9ea4bd00feb53bb574e5985f5d', 'dvepl', '61.0.17.215', '2023-11-20 10:08:38 AM', '', ''),
(96, '8cf3e0c52d9db89b53ba763062d23767', 'dvepl', '61.0.17.215', '2023-11-20 10:43:29 AM', '', ''),
(97, 'e55383cd31e4c10a576d88c57fc63a52', 'iqbal', '210.89.62.100', '2023-11-20 10:50:03 AM', '', ''),
(98, 'c4c90ef54cf787f4f60c00c84fff9e8d', 'dvepl', '117.210.195.184', '2023-11-20 12:21:07 PM', '', ''),
(99, '0bc2780249759a4dffa894a46a672034', 'dvepl', '210.89.62.100', '2023-11-20 12:38:21 PM', '', ''),
(100, '65459683058bb7ddea359501d69abe58', '', '210.89.62.100', '2023-11-20 15:17:32 PM', '', ''),
(101, '5cf724bf9a71b1cfd5d60cdfcf78d159', 'Maithili', '210.89.62.100', '2023-11-20 15:21:05 PM', '', ''),
(102, '968bddc473f525237fa6ee7497b799cd', 'dvepl', '61.0.197.101', '2023-11-20 15:26:50 PM', '', ''),
(103, 'b18e0cc719ada044bc508dc816dbb1e1', 'dvepl', '61.0.197.101', '2023-11-20 15:29:47 PM', '', ''),
(104, '52b0b48aa01424d4eadbb69d3e6e6943', 'iqbal', '210.89.62.100', '2023-11-20 18:01:09 PM', '', ''),
(105, '064765e8fa1161d49ff5d0f4c7769ae2', 'dvepl', '59.89.153.81', '2023-11-20 18:06:23 PM', '', ''),
(106, '7038ca469aa659ff9babd7a8dbbe26ef', 'iqbal', '210.89.62.100', '2023-11-21 09:42:02 AM', '', ''),
(107, '3ff018c6c6c812c8bf06d223b102dffe', 'dvepl', '117.203.237.70', '2023-11-21 10:08:20 AM', '', ''),
(108, 'd6e8c1984d7b68f3a4500bda73fe0db1', 'iqbal', '210.89.62.100', '2023-11-22 10:10:04 AM', '', ''),
(109, '063ce979983e1636dfd9a3eea6d20b67', 'dvepl', '49.156.97.230', '2023-11-22 11:04:29 AM', '', ''),
(110, 'd4dc14abadbe8d3ce91cd42b451f3a32', 'iqbal', '210.89.62.100', '2023-11-22 16:13:17 PM', '', ''),
(111, '47cab2ec5206a0313e14adb140c8ef17', 'dvepl', '210.89.62.100', '2023-11-23 10:51:03 AM', '', ''),
(112, 'e1c2892d6cc916ee035e617ed2a9a01e', 'dvepl', '2409:40d1:8d:4b15:8592:6446:8d22:ce40', '2023-11-23 12:04:35 PM', '', ''),
(113, 'cef71d10f66606f4ded6ba934ce6df0d', 'dvepl', '2409:4055:205:b6da:f1f5:2392:f0a8:68c8', '2023-11-23 16:11:36 PM', '', ''),
(114, 'a87e6ba48ac811795854d4f4e1162731', 'iqbal', '210.89.62.172', '2023-11-24 09:18:11 AM', '', ''),
(115, '1c0021e7b808b063749bc99f6f065c7e', 'dvepl', '2409:40d1:1009:266f:4dfa:81c:4f66:495', '2023-11-24 15:27:42 PM', '', ''),
(116, 'bc803c81acd4008022d2946c699004be', 'dvepl', '2409:40d1:1009:266f:4dfa:81c:4f66:495', '2023-11-24 15:28:40 PM', '', ''),
(117, '3b52c1fc7c6eca1741d9f1fae3f45a95', 'iqbal', '210.89.62.124', '2023-11-25 09:23:00 AM', '', ''),
(118, '11f90d25e34c411d6ece8126ad7fbe2b', 'dvepl', '202.14.121.177', '2023-11-26 20:42:38 PM', '', ''),
(119, 'e61074fb20ad6d0d97f1fac528daf968', 'iqbal', '210.89.62.124', '2023-11-28 09:19:24 AM', '', ''),
(120, 'fda767d5b0a819af54f337deca377a66', 'DVEPL', '185.213.82.188', '2023-11-28 09:34:18 AM', '', ''),
(121, '13835da591e7d139ec2bdfb325d99bd6', 'iqbal', '210.89.62.124', '2023-11-28 09:51:56 AM', '', ''),
(122, '4b3c353bdec1c8f9cc55400fbbda1a58', '', '210.89.62.124', '2023-11-28 12:48:10 PM', '', ''),
(123, 'cf5fdd1269dcc07ec1c478f53ba7e26d', 'dvepl', '2409:40d1:8e:a87b:c812:facb:8150:fbf4', '2023-11-28 14:47:54 PM', '', ''),
(124, '3479f70688eaeecc376ba98c032d85b2', '', '210.89.62.124', '2023-11-29 10:32:32 AM', '', ''),
(125, '846c815bf9636833e068f86f7bd56830', '', '210.89.62.124', '2023-11-29 10:38:28 AM', '', ''),
(126, '1a8a82d46c423a5b76984dacb01e6290', 'dvepl', '210.89.62.124', '2023-11-29 10:38:51 AM', '', ''),
(127, '482f17c13b018feefc1206aa2eafb224', 'iqbal', '210.89.62.124', '2023-11-29 11:32:58 AM', '', ''),
(128, '107f2a7a3534dce630fcedcc89cf743a', '', '117.203.233.225', '2023-11-29 12:04:50 PM', '', ''),
(129, '52864d11aec87148960d89308090e9e3', 'dvepl', '117.203.233.225', '2023-11-29 12:08:05 PM', '', ''),
(130, 'f3d0bb1084b5724993b5444515db6781', 'dvepl', '59.96.71.1', '2023-11-30 10:50:18 AM', '', ''),
(131, '7f556e936b3141675aa27c935335bf1d', 'dvepl', '2409:40d1:1011:744f:1cb9:8fe1:4f5a:7ee6', '2023-11-30 10:55:47 AM', '', ''),
(132, 'e3e332f31b1088113107ece8e87d993f', 'iqbal', '210.89.62.124', '2023-11-30 15:03:29 PM', '', ''),
(133, '3ae1e01d6bd3f9e0028aed645fce0dc5', 'iqbal', '210.89.62.124', '2023-12-01 10:53:24 AM', '', ''),
(134, '423e5165c04a3a93653be64fab963d62', 'dvepl', '61.0.18.71', '2023-12-01 11:05:54 AM', '', ''),
(135, 'b6ffd6ee3bce3c52bd1ff5dd08c41d7b', 'dvepl', '157.39.69.111', '2023-12-01 12:10:57 PM', '', ''),
(136, '2ddb2b33bf6dd2a19efe5bfe1619a649', 'dvepl', '49.156.97.3', '2023-12-01 14:02:06 PM', '', ''),
(137, '0e5076265c7a8612c824e647e4995302', 'iqbal', '210.89.62.124', '2023-12-01 16:26:53 PM', '', ''),
(138, 'b5b915a1bd65d2c60c566f051b3ec62c', 'iqbal', '210.89.62.148', '2023-12-02 09:41:02 AM', '', ''),
(139, '7b3988b7cd8c8a83ed36a99732c740bd', '', '61.0.199.138', '2023-12-02 10:36:54 AM', '', ''),
(140, '2030c9fc0be3f4bee7147b5652807b6c', 'DVEPL', '220.87.46.21', '2023-12-02 11:04:06 AM', '', ''),
(141, 'dfd033632c0323cb07f01928ee51de97', 'dvepl', '61.0.198.61', '2023-12-02 13:24:13 PM', '', ''),
(142, 'ea53ee2ceecaf3fd88295164d1f21f29', 'dvepl', '49.156.96.134', '2023-12-02 15:39:32 PM', '', ''),
(143, 'c378adb7a280f1858946ee11fcf01b9d', 'dvepl', '210.89.62.148', '2023-12-02 17:20:08 PM', '', ''),
(144, 'a8e643780a42439fac02d6bf49d9aaad', 'iqbal', '210.89.62.148', '2023-12-02 17:24:15 PM', '', ''),
(145, 'e46e8b9244bc47c77f1f19e8f6ef2881', 'iqbal', '210.89.62.148', '2023-12-04 09:26:11 AM', '', ''),
(146, '8b60d9c6543a39cae5a17d01b7413f44', 'dvepl', '210.89.62.148', '2023-12-04 10:35:22 AM', '', ''),
(147, '649840d1d5e560b0db479a97839f624e', 'dvepl', '117.203.231.220', '2023-12-04 11:07:04 AM', '', ''),
(148, '197c45737cca06105f176e8a1f57b19f', 'DVEPL', '147.192.97.232', '2023-12-04 22:27:31 PM', '', ''),
(149, 'd8841961ac4dbe35b5ebf551e3aada53', 'dvepl', '210.89.62.148', '2023-12-05 16:33:16 PM', '', ''),
(150, '4798eb2449c5132caf56294dce12adef', 'dvepl', '210.89.62.196', '2023-12-07 10:27:21 AM', '', ''),
(151, 'c1dbc054c05d8cd4422f954ab15830e4', 'dvepl', '210.89.62.196', '2023-12-07 10:35:07 AM', '', ''),
(152, '377798152bfa508d2609da56f00e8bc1', 'dvepl', '59.89.156.241', '2023-12-07 14:47:09 PM', '', ''),
(153, '0c73953c4b62f9be71839d5aae0b8059', 'iqbal', '61.0.201.159', '2023-12-07 17:28:06 PM', '', ''),
(154, '004409a63ec073fb82e67ebcf9b0b1f8', 'dvepl', '117.203.231.179', '2023-12-08 10:43:32 AM', '', ''),
(155, 'f356209e6cf2c330d9605d2a1a2691ef', 'iqbal', '117.203.226.143', '2023-12-08 12:50:20 PM', '', ''),
(156, 'ac7b58ea852866121915009d596efb39', 'dvepl', '2409:4055:312:fe6e:1590:3edb:a6ff:c836', '2023-12-08 17:19:11 PM', '', ''),
(157, '4e1aefb12a7977badeef48204d28aa31', 'iqbal', '202.14.121.254', '2023-12-09 10:14:49 AM', '', ''),
(158, '36bbd131ee87a8fe68dd373ec4d2a868', 'dvepl', '202.14.121.254', '2023-12-09 10:33:49 AM', '', ''),
(159, '25c5378fbe3de6ab8c6fcf903305a93d', 'dvepl', '202.14.121.254', '2023-12-09 11:19:53 AM', '', ''),
(160, '9b6bca063af53c102b78af64880b7bb9', 'dvepl', '49.156.98.165', '2023-12-09 18:16:22 PM', '', ''),
(161, 'e1333d939c131bdf6b88cbbdb9ce5ef7', 'iqbal', '202.14.121.254', '2023-12-11 09:42:41 AM', '', ''),
(162, 'b4241a78460d92a2a15d46797b9e4497', 'dvepl', '61.0.196.17', '2023-12-11 10:42:16 AM', '', ''),
(163, '9dcdc57afbd21fa9c280921efd080ea5', 'dvepl', '49.156.81.224', '2023-12-11 11:05:47 AM', '', ''),
(164, '41bfb32af6e99453f495d2a93c153b45', 'dvepl', '49.43.100.151', '2023-12-11 11:24:22 AM', '', ''),
(165, '8f3337d288ecd670de138929c5ac2f7a', 'iqbal', '202.14.121.254', '2023-12-11 12:14:45 PM', '', ''),
(166, '8db0cf319bf74b9361baa4ccb5ef84df', 'DVEPL', '117.203.234.174', '2023-12-11 14:17:47 PM', '', ''),
(167, '3bf1adae910845eb474f1901bf10f42d', 'dvepl', '117.203.234.174', '2023-12-11 15:45:37 PM', '', ''),
(168, '62d5443a2850358e27334658466176a2', 'dvepl', '49.156.82.80', '2023-12-11 18:44:23 PM', '', ''),
(169, 'f5d1825d54ea6722ae80f164a6cb22e9', 'dvepl', '49.156.82.80', '2023-12-12 00:05:27 AM', '', ''),
(170, '1199b776026262ccfc32f0a15ed52005', 'dvepl', '2409:40d1:e:64df:e8a2:6636:a0c7:6777', '2023-12-12 10:47:35 AM', '', ''),
(171, 'f8773e4f3720621e67adc8108601143a', 'dvepl', '49.156.82.80', '2023-12-12 11:19:04 AM', '', ''),
(172, '9615bfa282954e7a42d64c7086ea2eda', 'iqbal', '202.14.121.254', '2023-12-12 12:19:22 PM', '', ''),
(173, '324050bba77440a817c985909f13f5b8', 'dvepl', '49.156.107.134', '2023-12-12 21:23:35 PM', '', ''),
(174, '135d6df6e0a98c734e8c31889324fd53', 'dvepl', '49.156.89.104', '2023-12-14 00:16:04 AM', '', ''),
(175, 'b6177d1c66c2183d9574740d42ab0af6', 'iqbal', '202.14.121.254', '2023-12-15 09:51:37 AM', '', ''),
(176, '6564edfdb0175cdb34cbbd17f296e72b', 'dvepl', '202.14.121.254', '2023-12-15 12:13:32 PM', '', ''),
(177, 'bc6e461ad8bde3132dbaf00b7d35df87', 'dvepl', '202.14.121.254', '2023-12-18 11:50:06 AM', '', ''),
(178, 'ab01b6e330e91f77d7974061707ca62c', 'dvepl', '202.14.121.254', '2023-12-18 11:53:34 AM', '', ''),
(179, '31aa531b5de8f6b298b9fc8d0620ecbe', 'dvepl', '202.14.121.254', '2023-12-19 12:09:57 PM', '', ''),
(180, '63b3d27633aa89f58cf443419f7d7c53', 'dvepl', '122.173.27.73', '2023-12-19 17:47:33 PM', '', ''),
(181, '24428e86aee107d5d19c3a8d4ebdfd60', 'dvepl', '49.156.107.14', '2023-12-21 14:34:14 PM', '', ''),
(182, 'a0125286e20267b1f569c54787d456d9', 'iqbal', '202.14.121.14', '2023-12-21 15:22:03 PM', '', ''),
(183, 'e18d6c43624c3dd805c9f4e0924b3896', 'dvepl', '61.0.207.11', '2023-12-21 16:06:26 PM', '', ''),
(184, '9f9578f86366332e016a681683d9320b', 'dvepl', '2409:40d1:102f:8f1b:44eb:e7dc:4d27:c962', '2023-12-22 15:11:59 PM', '', ''),
(185, '62328901a85bf1cf78d195f42377ef90', 'dvepl', '2409:40d1:8a:6bb7:d979:d047:56db:ee8', '2023-12-27 11:13:20 AM', '', ''),
(186, 'e0b4e1e529127231698675178144c7c1', 'dvepl', '49.156.82.173', '2023-12-27 18:29:51 PM', '', ''),
(187, 'e2a16e5d84444144891324b9b61a008b', 'dvepl', '103.41.39.158', '2023-12-28 10:56:01 AM', '', ''),
(188, 'f29c78ec4966764f4d0b1ffd758eeb44', 'dvepl', '103.41.39.158', '2023-12-28 10:58:54 AM', '', ''),
(189, '3f332289b8926054107072d667ddc5f0', 'dvepl', '103.41.39.158', '2023-12-28 12:02:02 PM', '', ''),
(190, '1f5786e1fdef449da5fbda6e528e609e', 'iqbal', '103.41.39.158', '2023-12-29 11:25:34 AM', '', ''),
(191, 'c28c2d9543ae5179237028ffb649cfd4', 'dvepl', '49.156.107.0', '2023-12-29 13:47:56 PM', '', ''),
(192, '28f8e8f794e2ca33b69b1e26b1b69194', 'dvepl', '103.41.39.158', '2023-12-30 11:41:33 AM', '', ''),
(193, '08fda0c62a070c7bfecb069c5c43a8a8', 'iqbal', '103.41.39.158', '2023-12-30 16:35:06 PM', '', ''),
(194, 'ab3f530bdfd38c4ebaecba8e8f036c18', 'dvepl', '103.41.39.158', '2024-01-01 11:14:19 AM', '', ''),
(195, '59b8271973ddb35d069f9072fcbbcb8c', 'iqbal', '103.41.39.158', '2024-01-01 14:26:48 PM', '', ''),
(196, 'fb8e93b3f5d9af8bf79e4beeae3d66e5', 'dvepl', '2409:40d1:8d:9651:3523:de50:362f:6abf', '2024-01-02 11:01:55 AM', '', ''),
(197, 'bf4e97eb4f32c9490d336a7a642ac7dd', 'dvepl', '2401:4900:8161:4ab5:7492:4a:1793:8167', '2024-01-02 12:54:37 PM', '', ''),
(198, '24b2e2766f0cec040d87c358fbb09e15', 'dvepl', '2401:4900:8161:4ab5:ac17:c709:5adf:d173', '2024-01-02 14:10:20 PM', '', ''),
(199, '9607d6810d1f2135badf72025eae674d', 'dvepl', '2409:40d1:8d:9651:f070:2932:ab70:1558', '2024-01-02 14:17:35 PM', '', ''),
(200, 'efbd851519e544abc4135168a6d4c7a8', 'dvepl', '2409:40d1:8d:9651:f070:2932:ab70:1558', '2024-01-02 15:05:46 PM', '', ''),
(201, 'f02d40a5f3db97963abeea25178470b3', 'dvepl', '2409:40d1:8d:9651:f070:2932:ab70:1558', '2024-01-02 15:06:34 PM', '', ''),
(202, '4157448c65718c98b2de6b2a83e757fd', 'dvepl', '2401:4900:8161:4ab5:d5d3:f910:49ff:9b06', '2024-01-02 15:39:30 PM', '', ''),
(203, '0382ee4981198778d310aa1ad6785fa2', 'iqbal', '103.41.39.158', '2024-01-02 17:15:12 PM', '', ''),
(204, '899e3d71bc9b047e8e5b2b223c54d679', 'dvepl', '49.156.99.20', '2024-01-02 17:25:55 PM', '', ''),
(205, '292f7a46e6a548ebb35771c80af7f368', '', '2409:40d1:101c:818c:6512:2b8a:284a:f48a', '2024-01-03 12:57:34 PM', '', ''),
(206, '23123d8c08b9c3eb6ee0e3a2fe6520fa', 'dvepl', '2409:40d1:101c:818c:6512:2b8a:284a:f48a', '2024-01-03 13:00:12 PM', '', ''),
(207, 'f4fc0b0d28950e685cf365194fc213c7', 'iqbal', '103.41.39.86', '2024-01-03 15:07:36 PM', '', ''),
(208, '2d6115f827cb5de3fb17a9d5c17bb10c', 'dvepl', '61.2.83.245', '2024-01-03 16:04:41 PM', '', ''),
(209, '0c43ea4f1d77862edb126e8dad9e8b55', 'iqbal', '2401:4900:4703:3477:b99a:dc16:ae13:5467', '2024-01-04 10:38:48 AM', '', ''),
(210, 'dc965d1009c01e1b8447e587c39eb3ff', 'dvepl', '117.210.199.249', '2024-01-04 10:55:08 AM', '', ''),
(211, '980b64ba91c0be5784814295cc6cd6dc', 'DVEPL', '117.210.199.249', '2024-01-04 13:03:59 PM', '', ''),
(212, '5a1332b2c9bc1cee3f6363523c927a9c', 'dvepl', '117.210.199.249', '2024-01-04 13:53:52 PM', '', ''),
(213, '6eb4ec3e2e3b8388c3d13dcfa60bda2e', 'dvepl', '103.41.39.182', '2024-01-04 16:12:13 PM', '', ''),
(214, 'cb2bd48a7734228a7717dc287693a3d8', 'dvepl', '49.43.100.151', '2024-01-05 10:26:49 AM', '', ''),
(215, 'a33ff4a5df21526d92b40e416cf8906f', 'dvepl', '117.210.199.116', '2024-01-05 12:36:38 PM', '', ''),
(216, 'fe5e64076964c4b4b99b1e8dac546e7c', 'dvepl', '117.210.199.116', '2024-01-05 12:40:26 PM', '', ''),
(217, '4788e3166f23735d0ce03ac73b363117', 'dvepl', '49.156.84.241', '2024-01-05 12:49:57 PM', '', ''),
(218, 'aae668a1421d963bd84b420b13739d80', 'dvepl', '117.210.199.116', '2024-01-05 13:48:35 PM', '', ''),
(219, '57be11c577739e1b789950f2dcaa477a', '', '117.210.199.116', '2024-01-05 16:54:28 PM', '', ''),
(220, 'e52a656e9759fd584495878a327854cb', 'dvepl', '117.210.199.116', '2024-01-05 16:56:43 PM', '', ''),
(221, '70a6b1cfaee161362c1efe611d738a38', 'dvepl', '117.210.199.116', '2024-01-05 17:19:30 PM', '', ''),
(222, 'bb4709312e9735fa7319e222b7c89699', 'iqbal', '103.41.39.86', '2024-01-05 17:19:59 PM', '', ''),
(223, '08e71f708037af64db6ecbd6defc6573', 'dvepl', '117.210.199.116', '2024-01-05 18:09:14 PM', '', ''),
(224, '9aebc2329a9b93630acd083f0c4bf59a', '', '117.210.199.116', '2024-01-05 18:29:03 PM', '', ''),
(225, 'af8d467a2749e4dd875b7462013850a6', 'iqbal', '103.41.39.86', '2024-01-06 14:32:20 PM', '', ''),
(226, '75ec67749e7bdba5495da369c6c32410', 'dvepl', '132.154.151.185', '2024-01-06 17:26:50 PM', '', ''),
(227, 'c29513d1c41e3e2b038d726c70b4b043', '', '165.225.124.118', '2024-01-06 17:32:52 PM', '', ''),
(228, '3942327f3c653c3f0dc84e4b5013b1cd', 'dvepl', '49.156.82.116', '2024-01-07 01:31:51 AM', '', ''),
(229, '8d70baa74c30ebec23de138c593a7b16', 'dvepl', '49.156.99.219', '2024-01-07 18:44:10 PM', '', ''),
(230, '90ded499177e590bfd3fc29204d9e77b', 'dvepl', '49.156.99.219', '2024-01-07 18:48:30 PM', '', ''),
(231, 'cc76d72dd68a11c6a27370af81da6df0', 'dvepl', '61.0.202.251', '2024-01-08 09:23:35 AM', '', ''),
(232, 'd65ee8488a30c137c835d4b9d1d37d13', 'dvepl', '49.156.93.41', '2024-01-08 12:21:44 PM', '', ''),
(233, '70d489216283d9859098ec67640b1f66', 'iqbal', '103.41.39.134', '2024-01-08 12:27:28 PM', '', ''),
(234, '11bb6b0dd6b74386733e4ecf39a64aed', 'dvepl', '49.156.93.41', '2024-01-08 12:45:22 PM', '', ''),
(235, 'e8054aba8727312b1944271ed8a6083d', 'iqbal', '103.41.39.134', '2024-01-08 15:22:33 PM', '', ''),
(236, '3b6e007a60953648ac714e2fb0e34466', 'dvepl', '49.156.93.41', '2024-01-08 17:29:31 PM', '', ''),
(237, '60188dee15d33c7dc29fa102b18f90f7', 'dvepl', '61.0.197.239', '2024-01-09 09:15:46 AM', '', ''),
(238, 'fac9c20d6a1b11d59e3fbc08dd75fa55', '', '59.89.152.149', '2024-01-09 11:41:40 AM', '', ''),
(239, 'efa8f95a21b3c4b063f9aae47a82ba4b', 'dvepl', '59.89.152.155', '2024-01-09 12:07:02 PM', '', ''),
(240, '9fe6a6e1fc9ec5b013c7f98b408a4b37', 'iqbal', '103.41.39.134', '2024-01-09 15:14:02 PM', '', ''),
(241, '6643986ffd9ce86be3d3f3a9d9f6be98', 'dvepl', '49.156.103.207', '2024-01-09 16:08:15 PM', '', ''),
(242, 'f8507da30dacde5706c26e25f9a73ca7', 'dvepl', '49.156.103.207', '2024-01-09 16:48:41 PM', '', ''),
(243, '3d1c43166fe81fa641b79e75a6a110b5', 'dvepl', '59.89.152.155', '2024-01-09 16:57:21 PM', '', ''),
(244, '4d9841903a61f7ab182495b6833af38e', 'iqbal', '103.41.39.134', '2024-01-09 17:50:35 PM', '', ''),
(245, 'bf54e610187b21cc6a683977f59bd5dc', 'dvepl', '2409:4054:2194:9536:8142:4f63:bd28:660b', '2024-01-10 06:50:18 AM', '', ''),
(246, '231bc0434ca89e310d7fb2cffe982905', 'dvepl', '59.89.152.155', '2024-01-10 09:33:43 AM', '', ''),
(247, 'b73c69e394427fe2f75b8c535e0a6216', 'dvepl', '103.41.39.182', '2024-01-10 12:00:01 PM', '', ''),
(248, 'fe8ec9e2a802601319d87e47be1918f0', 'dvepl', '103.41.39.182', '2024-01-10 14:30:59 PM', '', ''),
(249, '4e7d95d7dbe7f45f822a6f205ba6253b', 'iqbal', '103.41.39.182', '2024-01-10 14:36:20 PM', '', ''),
(250, '2ae940e4c83ee175fb91f53ca13facb5', 'iqbal', '103.41.39.182', '2024-01-10 17:05:02 PM', '', ''),
(251, '1dd7872cc3f30ec2064ea923bd3b9a92', '', '114.31.141.134', '2024-01-10 19:20:45 PM', '', ''),
(252, 'a8a4b60b8717ba58fd3ec83b481f9fbd', 'dvepl', '117.203.233.219', '2024-01-11 09:51:06 AM', '', ''),
(253, '235c2adb6153ea5a1925ac96052b59cf', '', '165.225.124.95', '2024-01-11 10:00:22 AM', '', ''),
(254, 'fc9b89bb2e4471648f841abcef8b58c4', 'iqbal', '103.41.39.182', '2024-01-11 10:19:28 AM', '', ''),
(255, '4d080fd2308ce4eaed3836d38df9cb97', 'dvepl', '59.89.155.25', '2024-01-12 09:31:13 AM', '', ''),
(256, 'f5fa190957826e94694297f06465e167', 'iqbal', '103.41.39.182', '2024-01-12 09:37:45 AM', '', ''),
(257, 'e01pkf1qcai5bd4g9hoiac1ogv', '', '::1', '2024-01-13 16:39:07 PM', '', ''),
(258, '50a512r9k4md156j31bom8pls4', 'janmitha', '::1', '2024-01-13 16:43:35 PM', '', ''),
(259, '7isp1n3rvq93unn6mhpv4r8dvt', 'janmitha', '::1', '2024-01-13 16:53:49 PM', '', ''),
(260, 'vr2nceuuj9atqgigfchkjv0c5k', 'janmitha', '::1', '2024-01-13 19:26:26 PM', '', ''),
(261, '4pn3ogcifv4vf4h275t4b2kn37', 'janmitha', '::1', '2024-01-21 18:09:15 PM', '', ''),
(262, 'b1fdbbc874cf59755c7be523960936c4', 'dvepl', '49.156.99.3', '2024-01-21 20:48:11 PM', '', ''),
(263, '805d69ddafd59e4dccf165861c07b1d3', 'dvepl', '157.39.69.218', '2024-01-21 21:24:51 PM', '', ''),
(264, '31614ae1175e0bbb92a6e27a78a9141b', 'dvepl', '49.156.99.3', '2024-01-22 01:44:24 AM', '', ''),
(265, 'b095dc6288c4a3919bdd1a82a04bb059', 'iqbal', '103.41.39.110', '2024-01-22 11:27:30 AM', '', ''),
(266, '6cbc3bbd485d549342f90b69840bbecc', 'dvepl', '117.203.234.207', '2024-01-22 11:32:13 AM', '', ''),
(267, '01bf3f1f9668033d07332f3acd36a261', 'iqbal', '103.41.39.110', '2024-01-22 11:40:26 AM', '', ''),
(268, 'ba76ea8711c37ee6b627c1c2410bfc1e', 'DVEPL', '59.96.68.245', '2024-01-22 12:18:27 PM', '', ''),
(269, '6b90b0a424501d11922b206aecf7e51a', 'dvepl', '49.156.109.14', '2024-01-22 12:38:01 PM', '', ''),
(270, '8507570faed4ec1b86ff9a7d90714a05', 'dvepl', '49.156.109.14', '2024-01-22 13:24:45 PM', '', ''),
(271, '2e4248254385ef86886d9c58fa2c819a', '', '59.96.68.245', '2024-01-22 14:06:01 PM', '', ''),
(272, '9faa31d8be7fbedbcf77468f590e08fb', 'dvepl', '59.96.68.245', '2024-01-22 14:11:44 PM', '', ''),
(273, '2a09798a35cd7bfd56433c84d2b6058c', 'dvepl', '59.96.68.245', '2024-01-22 14:32:01 PM', '', ''),
(274, '5c7c70069335f4ddde15580aab0a7c9e', 'dvepl', '59.96.68.245', '2024-01-22 14:35:42 PM', '', ''),
(275, '42cc2dc3983968350b47d2608e366ca9', 'dvepl', '59.96.68.245', '2024-01-22 15:35:29 PM', '', ''),
(276, '4c44a713fbb9bafbf5a7e4326211567f', 'iqbal', '103.41.39.110', '2024-01-22 15:54:02 PM', '', ''),
(277, 'c2bdf6edf9faa8acec9017cb82cc199c', 'dvepl', '49.156.109.14', '2024-01-22 16:02:29 PM', '', ''),
(278, '2a034db19bce27b176f5092210357b2a', 'dvepl', '49.156.109.14', '2024-01-22 16:08:48 PM', '', ''),
(279, 'a12061c301c8f8f3b78de8d42848f5ab', 'janmitha', '49.156.109.14', '2024-01-22 18:06:46 PM', '', ''),
(280, '7fd781f847ceb7a6301941cabc3bd3c8', 'dvepl', '103.41.39.129', '2024-01-23 03:45:47 AM', '', ''),
(281, 'd904ac99847b643c020ab9804836d02a', 'dvepl', '103.41.39.129', '2024-01-23 03:47:19 AM', '', ''),
(282, 'b74c27767b03825beb22fbe8275c8071', 'iqbal', '103.41.39.110', '2024-01-23 09:30:31 AM', '', ''),
(283, '62ee72d7a9ddd965be3d6141976e148a', 'dvepl', '125.62.126.81', '2024-01-23 10:34:13 AM', '', ''),
(284, 'b919111c1d35597059476d541ee10ebf', '', '125.62.126.81', '2024-01-23 10:35:19 AM', '', ''),
(285, '283cc4ea31d2a283accd45bf9d538ebe', 'dvepl', '125.62.126.81', '2024-01-23 10:36:26 AM', '', ''),
(286, '8f1c11d0bea62ccbe081ce46b115d1ae', 'dvepl', '125.62.126.81', '2024-01-23 11:29:01 AM', '', ''),
(287, '652ca65de469d9e1994b40244628e499', 'dvepl', '117.203.228.75', '2024-01-24 14:42:57 PM', '', ''),
(288, 'db155971d78c0af144a379c904e7f24f', '', '103.41.39.110', '2024-01-24 17:09:23 PM', '', ''),
(289, 'e7f7723f6f75a1ff92f6607db6a98a23', 'iqbal', '103.41.39.110', '2024-01-24 17:10:35 PM', '', ''),
(290, 'e4c2ad4a1b3ca4410f6eba92a8ed1b47', 'dvepl', '103.41.39.201', '2024-01-25 06:14:55 AM', '', ''),
(291, '7901a1ac9cb80432f9fc823fda96b5c4', 'dvepl', '103.41.39.201', '2024-01-25 06:16:26 AM', '', ''),
(292, '461b40351dfbc3357f7797417e18f7c8', 'dvepl', '2409:40d1:88:fc70:429:3dcc:ec9c:5a8', '2024-01-25 13:55:53 PM', '', ''),
(293, '34197b6e4716fb8e86559a6897424b3a', 'dvepl', '61.0.200.52', '2024-01-27 13:54:28 PM', '', ''),
(294, 'daea4c060e9ec5694d1f2eb874073c94', 'dvepl', '61.0.200.52', '2024-01-27 13:59:52 PM', '', ''),
(295, '91a3cc92edd144c6299b9fec3f7aa231', 'dvepl', '125.62.127.156', '2024-01-27 18:00:48 PM', '', ''),
(296, 'ce85e01cf2409e122b99be4254466ff3', 'dvepl', '49.156.102.160', '2024-01-29 00:22:07 AM', '', ''),
(297, '6e4ea16b5afe50980d9eeafe838b53af', '', '103.41.39.206', '2024-01-29 10:16:08 AM', '', ''),
(298, '51b52502a61957c10adbc2c13bea9630', '', '103.181.57.58', '2024-01-29 10:32:01 AM', '', ''),
(299, 'd219cdf758b1b2ba304a81c3ca4c89da', '', '103.181.57.58', '2024-01-29 10:52:13 AM', '', ''),
(300, '41eff6bdcd8a50805570edcc8378209e', 'iqbal', '103.41.39.206', '2024-01-29 12:09:26 PM', '', ''),
(301, '79b57305c5f19c548c09949247190fb0', 'dvepl', '61.0.200.52', '2024-01-29 12:53:08 PM', '', ''),
(302, 'ca8f7dcea6e65cc078662672d68fbe25', '', '103.125.235.24', '2024-01-29 16:32:14 PM', '', ''),
(303, 'ab68e79802fc7cbad5b08baa602c1ea0', '', '103.125.235.24', '2024-01-29 17:01:23 PM', '', ''),
(304, 'b9a48540954439e60f9ebd70cd6ed6d6', '', '49.43.100.121', '2024-01-30 13:07:55 PM', '', ''),
(305, 'e333a9483a04e8537320872e8d601a23', 'dvepl', '49.43.100.121', '2024-01-30 13:08:10 PM', '', ''),
(306, 'ee13b0e6e8fe2937a0dcdc61c12358a4', 'dvepl', '49.156.110.175', '2024-01-30 17:39:36 PM', '', ''),
(307, '063c60b6200dae2a4b30848a18c84970', 'dvepl', '49.156.110.175', '2024-01-30 19:00:52 PM', '', ''),
(308, '4b97ece2cdc2fdc1799443ef68ba937d', 'dvepl', '49.156.110.175', '2024-01-30 19:22:31 PM', '', ''),
(309, '9e4511e726bca3eeb6ba2e3bdd30e3d7', 'dvepl', '49.156.110.175', '2024-01-30 19:23:57 PM', '', ''),
(310, 'bfa28552bdc46f54b3d74f3d2dba456a', 'dvepl', '49.156.110.175', '2024-01-30 23:53:58 PM', '', ''),
(311, '6f92a631d9f208b8ce6d4f929627f09f', '', '49.156.110.175', '2024-01-30 23:55:10 PM', '', ''),
(312, '68a12ce1aaf5f95543550edf9ff8ca58', 'dvepl', '49.156.110.175', '2024-01-30 23:56:31 PM', '', ''),
(313, '1186c9833e22615645bcea65a69745fe', '', '103.41.39.35', '2024-01-31 09:20:08 AM', '', ''),
(314, 'd2247c07a3c4a519144780b8009cfa78', 'iqbal', '103.41.39.158', '2024-01-31 09:22:16 AM', '', ''),
(315, 'd313ecfe1097ff6f68c80486f18db7d4', 'dvepl', '157.39.75.99', '2024-01-31 12:51:00 PM', '', ''),
(316, '4ccb25255a0574305d165ccf8ef49ad7', 'dvepl', '157.39.75.99', '2024-01-31 12:51:44 PM', '', ''),
(317, 'fc47b54460662f69eee4a90657dfbedd', '', '103.41.39.158', '2024-01-31 14:34:36 PM', '', ''),
(318, '8cdb66b51e5157a789b010850724fbd3', 'iqbal', '103.41.39.158', '2024-01-31 14:36:19 PM', '', ''),
(319, 'a8fb73c78abb6c8794beeb33cb3573b7', '', '2401:4900:80af:39c:489f:7dff:fe27:74de', '2024-01-31 14:50:25 PM', '', ''),
(320, 'df2b6461e418a614180ed774c2e21908', '', '45.87.213.229', '2024-01-31 16:28:55 PM', '', ''),
(321, '1fad1edf07dacf60957b772c71b701c6', 'dvepl', '49.156.93.172', '2024-01-31 18:12:32 PM', '', ''),
(322, '6a16b55e39aa748e4b344e262df6e91a', '', '103.181.57.106', '2024-01-31 20:03:51 PM', '', ''),
(323, '136521e60d5f5a48b235425aa5d7f430', 'dvepl', '49.156.102.195', '2024-02-01 11:05:01 AM', '', ''),
(324, '315b5b6d85de2ec7b05cab2a79e47394', 'dvepl', '49.156.102.195', '2024-02-01 11:06:44 AM', '', ''),
(325, '842d084258638e69a96e834b21f3b5e3', 'dvepl', '49.156.102.195', '2024-02-01 18:25:59 PM', '', ''),
(326, 'b39c310cf1066c3b0e89e22c24965d86', 'iqbal', '223.130.29.38', '2024-02-05 11:01:53 AM', '', ''),
(327, '2a95ca630be7177f293605e674b30adb', 'dvepl', '49.156.102.193', '2024-02-06 01:14:41 AM', '', ''),
(328, '951efa270be19acc86fe0ffb2473bab2', 'dvepl', '49.156.102.193', '2024-02-06 01:15:58 AM', '', ''),
(329, '92f4561874035e7488b17610c57415fe', '', '49.156.88.125', '2024-02-06 19:03:00 PM', '', ''),
(330, 'c36da365de20b4cdb5fb724095ccaeb8', 'dvepl', '49.156.88.125', '2024-02-06 19:04:04 PM', '', ''),
(331, '37e66af0143357ca67e0b2d8cc892b30', '', '2401:4900:8141:4bf3:140b:9c7c:3838:c0c7', '2024-02-07 16:09:24 PM', '', ''),
(332, 'bb25cf6a33778fefe2284799cdc7b9c6', '', '103.18.71.11', '2024-02-08 09:04:33 AM', '', ''),
(333, '42104a3f8c2c4aa3eaf75263e166e601', '', '2409:4055:192:c9af:4191:f72d:8065:e4fe', '2024-02-08 13:41:52 PM', '', ''),
(334, 'c16bd466b8ad04bf0297f0bf695eab4f', 'iqbal', '223.130.29.38', '2024-02-09 14:50:38 PM', '', ''),
(335, 'caf94bc535e33c4eaf69f6af1d00f175', 'dvepl', '49.156.83.87', '2024-02-13 19:18:30 PM', '', ''),
(336, '515eecc68ea25f1e8005b4bc048a0729', 'dvepl', '49.156.83.87', '2024-02-13 19:25:24 PM', '', ''),
(337, 'f22308f4544bc431538cb0268886dae6', 'dvepl', '49.156.83.87', '2024-02-13 19:26:33 PM', '', ''),
(338, '374bf72c7ce5cd304ab726e1323d07bd', 'iqbal', '210.89.58.200', '2024-02-14 15:56:03 PM', '', ''),
(339, '875686be86d18fe58bfe4ea245db60b5', 'dvepl', '2401:4900:81ed:a451:a57d:c882:cfe7:418b', '2024-02-15 10:47:48 AM', '', ''),
(340, '875686be86d18fe58bfe4ea245db60b5', 'dvepl', '2401:4900:81ed:a451:a57d:c882:cfe7:418b', '2024-02-15 10:47:51 AM', '', ''),
(341, '8bcbac67e7d9ee0d53e25598a0b4d962', 'dvepl', '27.255.223.199', '2024-02-15 11:53:53 AM', '', ''),
(342, '9d8a73ac3a49d49bac017558efef8e26', 'dvepl', '2409:4051:4e83:c739:a568:14a0:31e7:b28c', '2024-02-15 11:53:54 AM', '', ''),
(343, 'ed53ea8a4457fc56559a6f41e5cae11a', 'dvepl', '27.255.223.199', '2024-02-15 11:55:59 AM', '', ''),
(344, '27be155522f0ddb53f4dbb9309f7dbb8', 'dvepl', '2409:4051:4e83:c739:a568:14a0:31e7:b28c', '2024-02-15 11:57:00 AM', '', ''),
(345, '3c1e0e4d0e5c547cd560f3d7ff1ebe07', 'dvepl', '42.109.212.39', '2024-02-15 22:57:13 PM', '', ''),
(346, 'b6a1535f0ef6b1c617f4e3bf856a5255', 'dvepl', '49.156.107.126', '2024-02-16 16:52:37 PM', '', ''),
(347, 'c5bbb61eb1b0f48fc133ca994d213e5b', 'dvepl', '2409:4051:4e04:6db7:e8a1:ab2f:7664:5daf', '2024-02-16 18:27:08 PM', '', ''),
(348, '36ef7f4143b2c90b349e36cd4db5466c', 'dvepl', '2409:4051:2eca:72b2:18c3:bb02:366:85ea', '2024-02-17 17:18:22 PM', '', ''),
(349, 'f63ce14b6cb5b1a15ceebbaffcca55a0', 'iqbal', '210.89.58.200', '2024-02-19 12:30:27 PM', '', ''),
(350, 'b104999ca4a55bd15c759ff50cce9697', 'iqbal', '210.89.58.200', '2024-02-20 09:04:04 AM', '', ''),
(351, '87fe4504ab026ee5652a0e91b3ebce1b', 'dvepl', '125.62.119.154', '2024-02-20 14:32:16 PM', '', ''),
(352, '5182b68bcc8c2e5d4c157f6755f45046', 'dvepl', '2409:4051:2eca:72b2:1102:584f:5c00:f22f', '2024-02-21 12:03:23 PM', '', ''),
(353, '790d24a56960ead12f913ff582194efb', 'dvepl', '49.156.100.206', '2024-02-22 11:26:53 AM', '', ''),
(354, 'dd203d21a824066a33c2927ac4ec550d', 'dvepl', '2409:4051:2eca:72b2:51:9156:4e96:b023', '2024-02-22 14:57:06 PM', '', ''),
(355, '452b5f8d57ac6a2e15ff71a9de0504de', 'janmitha', '49.156.73.200', '2024-02-22 16:56:33 PM', '', ''),
(356, '37efc937dcbbd67c63c04eaf4bf5f8a2', 'dvepl', '49.156.73.200', '2024-02-22 16:56:51 PM', '', ''),
(357, '8330825892d3de6f86266aea222b30de', 'janmitha', '49.156.73.200', '2024-02-22 16:57:36 PM', '', ''),
(358, '0253cf95a019b424a042ffe191ea9878', 'dvepl', '2409:4051:2eca:72b2:51:9156:4e96:b023', '2024-02-22 17:59:00 PM', '', ''),
(359, 'a176afabe834727e375373bfba064d38', 'janmitha', '2409:4051:2eca:72b2:890d:c26b:debc:1863', '2024-02-22 23:40:47 PM', '', ''),
(360, 'b8562cf2f45f435f3c6affc5517ce173', 'dvepl', '2409:4051:2eca:72b2:890d:c26b:debc:1863', '2024-02-22 23:41:10 PM', '', ''),
(361, '97d399eef4674415cb277e3e6a776ed5', 'dvepl', '49.156.73.200', '2024-02-23 01:19:46 AM', '', ''),
(362, '04e9e7b37e07e379a616d38e02db95d7', 'dvepl', '125.62.124.102', '2024-02-23 14:25:57 PM', '', ''),
(363, '4794ca58aedec2007c00de8a366a2a1e', 'dvepl', '2409:4051:2eba:4fe9:a1ab:99af:3f57:fbc6', '2024-02-23 14:49:15 PM', '', ''),
(364, '3374a763a0a2b22d74ffbade4973771b', 'iqbal', '210.89.58.210', '2024-02-23 17:07:56 PM', '', ''),
(365, 'bc153986995e5035582552b7fa5891c9', 'iqbal', '210.89.58.210', '2024-02-24 09:23:47 AM', '', ''),
(366, 'e18020eae6ce39c9e4fb61a0e14ea8ed', 'iqbal', '210.89.58.210', '2024-02-24 14:14:45 PM', '', ''),
(367, 'faf4519f76c6b2b70d9a78cec4ffb04f', 'dvepl', '2409:4051:2eba:4fe9:44dd:ffa7:6313:a117', '2024-02-24 15:59:25 PM', '', ''),
(368, 'c825405546d6e28f4fff27aabaa7a5b0', 'dvepl', '49.156.88.194', '2024-02-25 00:59:08 AM', '', ''),
(369, '836fbf56ead24140b7eea0fd135589e9', 'dvepl', '122.173.30.120', '2024-02-25 13:25:55 PM', '', ''),
(370, '7d7c952d2c6d34cacca8d19f615064d0', 'dvepl', '2409:4051:2eba:4fe9:8951:ea57:46ba:55b1', '2024-02-25 23:01:50 PM', '', ''),
(371, '23ef0bd2396d59a28c6ccd46feb79a90', 'dvepl', '2409:4055:417:de9b:c5e8:1f1b:b78:2f6', '2024-02-26 07:42:44 AM', '', ''),
(372, 'efbf388d8a289805a02b463f423bb7eb', 'dvepl', '2409:4054:2200:d0da:bca7:70c8:7d0a:effe', '2024-02-26 09:21:19 AM', '', ''),
(373, '182c4c8a39423f446872aaee0986f23c', '', '2401:4900:1f33:b262:5df3:2a7e:b6fd:f02e', '2024-02-26 16:09:02 PM', '', ''),
(374, '50b8bbbb51fa7231e00e0286cee5c9db', 'dvepl', '49.156.76.123', '2024-02-27 01:41:00 AM', '', ''),
(375, '3895fbda838cd615987946153ba217a6', 'dvepl', '49.156.76.123', '2024-02-27 01:47:06 AM', '', ''),
(376, '637867b049cd740ed86bd5744ff9b10c', 'dvepl', '49.156.76.123', '2024-02-27 01:53:59 AM', '', ''),
(377, 'cb4ddb1a55ead6e9773c26e6adaf0bc1', 'dvepl', '157.39.74.244', '2024-02-27 05:48:12 AM', '', ''),
(378, 'e964565f9ba24b3cf5af17461bab6a0a', 'dvepl', '42.109.200.18', '2024-02-27 12:56:08 PM', '', ''),
(379, 'eedbe45a5656a2161b7d057378cac74c', 'dvepl', '49.156.98.174', '2024-02-29 10:48:07 AM', '', ''),
(380, '30f3b26e8135e79c1e51445aeb079912', 'dvepl', '2409:40d1:102f:4d69:8104:16e1:2fdb:5539', '2024-03-01 13:57:52 PM', '', ''),
(381, '878bd1dce0b62f898407a5b7aec7cad2', 'dvepl', '61.247.249.74', '2024-03-05 13:12:27 PM', '', ''),
(382, '84616a2aeb214e189901e6d33b366c5b', 'iqbal', '210.89.58.213', '2024-03-09 17:28:13 PM', '', ''),
(383, '7be55eb08533b82dc2bdcb54edf2ea04', 'iqbal', '210.89.58.213', '2024-03-11 10:28:25 AM', '', ''),
(384, '307905dd6bf3783594ba99c183bb6f11', 'dvepl', '2409:40d1:10:279c:79dc:acd5:ef1f:3e04', '2024-03-13 11:06:46 AM', '', ''),
(385, '9a233ad71c3acfcc093f443d7eb5d2d1', 'dvepl', '2409:40d1:0:6116:b174:811a:3f44:df21', '2024-03-14 11:22:52 AM', '', ''),
(386, 'de203edf56fdfadf783eebda5a8fb4f0', 'dvepl', '180.188.251.126', '2024-03-17 17:00:53 PM', '', ''),
(387, 'bc4a1c5766e417388140cc81e7966aa5', 'dvepl', '2405:201:5023:4068:f512:9c5f:9a04:c39c', '2024-03-21 18:17:41 PM', '', ''),
(388, '897ea2a17b7a27200cd74d663fceabdb', 'dvepl', '2405:201:302c:f006:d4aa:cc0:eb17:1bcc', '2024-03-21 18:18:23 PM', '', ''),
(389, '44bac7ac89701484bf8967f25ad4487b', 'dvepl', '2405:201:5023:4068:c923:949:9083:9702', '2024-03-23 15:43:45 PM', '', ''),
(390, 'a39942e7418ce4e4b0a4593b688a2e06', 'dvepl', '42.109.192.251', '2024-03-24 09:06:14 AM', '', ''),
(391, '3e46eebc7e84e1c1c8757a9f46c9d62b', 'dvepl', '59.89.158.203', '2024-04-01 23:19:36 PM', '', ''),
(392, 'ef4935b8b4006b97269667609f556ef8', 'dvepl', '2405:201:5023:4064:e8d0:e053:9f59:2ef9', '2024-04-02 17:56:13 PM', '', ''),
(393, '71e862dd9721363957284afd4b473dbf', 'arun', '2405:201:5023:4064:e8d0:e053:9f59:2ef9', '2024-04-02 17:57:08 PM', '', ''),
(394, 'd26ab16c76e72defd1e20498159c2ab1', 'dvepl', '2405:201:5023:4064:e8d0:e053:9f59:2ef9', '2024-04-02 17:57:32 PM', '', ''),
(395, 'be8e21df25da6da259fc8a2eed173b4c', 'arun', '2405:201:5023:4064:e8d0:e053:9f59:2ef9', '2024-04-02 17:58:08 PM', '', ''),
(396, 'c6bc5c536b13758b54762d9ec8a218ad', 'dvepl', '59.96.70.241', '2024-04-03 17:17:21 PM', '', ''),
(397, '6c69fbb6f3ec60a982ca97ec614088ef', 'dvepl', '2409:40d1:102b:2cfb:5c86:9067:3948:8da8', '2024-04-10 09:44:38 AM', '', ''),
(398, '0a5dbd3c17ed8fe0a254aaedbc884d4b', 'DVEPL', '5.62.34.47', '2024-04-11 08:58:17 AM', '', ''),
(399, '552a40fcf2683ea5a37f308af2b0d191', 'dvepl', '223.177.205.205', '2024-04-18 16:40:35 PM', '', ''),
(400, '5262312e7887246665014c8cf0de4a46', 'dvepl', '2401:4900:599d:6f01:581e:7e56:e958:473b', '2024-04-20 21:37:04 PM', '', ''),
(401, '296739f5646498cf4857db5e823112e2', 'dvepl', '223.178.210.94', '2024-05-01 16:23:35 PM', '', ''),
(402, '980686b3d8030afd38808ff3e7053b49', 'dvepl', '223.178.208.248', '2024-05-01 17:09:54 PM', '', ''),
(403, 'ab1c6ab6785dec87ecf9f590e0743c9f', 'dvepl', '2405:201:5023:4827:f8d7:23fd:e2e0:78cc', '2024-06-05 14:51:15 PM', '', ''),
(404, 'f23a0b2511833d77436def544af74651', 'rajat', '2405:201:5023:4827:f8d7:23fd:e2e0:78cc', '2024-06-05 14:52:01 PM', '', ''),
(405, 'b3f95285bbe7d03ea25cdf2ace04249e', 'dvepl', '2405:201:5023:4827:f8d7:23fd:e2e0:78cc', '2024-06-05 14:52:46 PM', '', ''),
(406, '594ca2ef50fd5088ada3f670e6784e90', 'dvepl', '117.203.232.181', '2024-06-09 11:02:41 AM', '', ''),
(407, 'f64b8cb3a25f16e5e8649e14d814db97', 'dvepl', '61.2.83.45', '2024-06-10 07:03:04 AM', '', ''),
(408, '12f0d9b152a89d891b7020b63323aee4', 'dvepl', '59.99.152.6', '2024-06-13 15:08:07 PM', '', ''),
(409, 'eabf32e6de45c18e39eb0823bd9c6a66', 'dvepl', '59.99.152.6', '2024-06-13 15:20:14 PM', '', ''),
(410, '14a1b85b4d499b3251508739bdc3a9df', 'dvepl', '59.99.152.6', '2024-06-13 15:27:40 PM', '', ''),
(411, '4b991de4c32a5a2541aa180473b97e10', 'Anuradha', '59.99.152.6', '2024-06-13 15:28:34 PM', '', ''),
(412, 'a9efa32da83c07a18cc0e421051dff4a', 'dvepl', '59.99.152.6', '2024-06-13 15:30:42 PM', '', ''),
(413, '54880166a3d7f06bfbe2611a22f15b21', 'Anuradha', '38.183.50.183', '2024-06-13 15:39:54 PM', '', ''),
(414, '8127dbe0bef4c5b49c9d8ba75cf6d6a5', 'Anuradha', '38.183.28.236', '2024-06-14 09:05:20 AM', '', ''),
(415, '62b7cfa788c69d376d68c3b14903c4ca', 'dvepl', '223.178.212.122', '2024-06-14 13:45:20 PM', '', ''),
(416, '6d1d7c701347af204e5a53e44dae8617', 'Anuradha', '38.183.24.225', '2024-06-15 09:06:27 AM', '', ''),
(417, '6efbadb60cf8454c164ae0f7e7affb33', 'dvepl', '59.99.152.6', '2024-06-15 11:53:08 AM', '', ''),
(418, 'a613d77b500e91842a2c00c1d6834f50', 'Anuradha', '38.183.24.225', '2024-06-15 12:52:20 PM', '', ''),
(419, '9d70c860b5e9606fdd69a353d0a366eb', 'dvepl', '38.183.34.126', '2024-06-17 09:35:00 AM', '', ''),
(420, '7eb2637584c1a1e8b8ba2595573e94b5', 'Anuradha', '59.99.152.6', '2024-06-17 11:18:09 AM', '', ''),
(421, '0b92c9f3f5456914628b2f7c9830bc71', 'Anuradha', '59.99.152.6', '2024-06-17 16:46:46 PM', '', ''),
(422, '716029d286deb399f1ff494e4a36a55d', 'Anuradha', '38.183.35.6', '2024-06-18 10:33:56 AM', '', ''),
(423, '1abe1ed0e70e314f3941f67444a86fc8', 'dvepl', '157.39.66.19', '2024-06-19 07:09:01 AM', '', ''),
(424, '7e6677923caa9fc3b900c8395d90949a', 'Anuradha', '38.183.38.184', '2024-06-19 09:42:15 AM', '', ''),
(425, 'eb586e13174435bbe33d50771c4f9416', 'iqbal', '38.183.38.184', '2024-06-19 12:43:46 PM', '', ''),
(426, 'fb97f06be137efdbb8fb4c69c91e9bd1', 'Anuradha', '38.183.31.137', '2024-06-20 11:14:23 AM', '', ''),
(427, '74757bca9e29f608395a6eed74c9cf29', 'iqbal', '38.183.31.137', '2024-06-20 11:38:25 AM', '', ''),
(428, 'ce7243dd7fc8a63a0f2247ea3380a0f0', 'Anuradha', '38.183.31.137', '2024-06-20 11:54:54 AM', '', ''),
(429, 'c272b2a1930ec25159282c715d300bc5', 'Anuradha', '38.183.31.137', '2024-06-20 13:36:14 PM', '', ''),
(430, 'b15879fee2f16eb78361440ed567ca2d', 'dvepl', '157.39.248.213', '2024-06-20 15:54:33 PM', '', ''),
(431, '3f920ba33b4871fa118c7b72e29b5a3c', 'iqbal', '38.183.30.186', '2024-06-20 16:45:41 PM', '', ''),
(432, '67f7cf85b7ce5e374f62c601acc6d3d4', 'Anuradha', '38.183.32.234', '2024-06-21 09:07:38 AM', '', ''),
(433, 'd6f820c41955c586f8ffc6863d9851d5', 'Anuradha', '38.183.32.234', '2024-06-21 09:08:01 AM', '', ''),
(434, '2cd727458ac3087cfb90bae1038af83c', 'dvepl', '2409:4055:18b:e2ff:98c7:f6b4:a5d7:b5ed', '2024-06-22 14:22:22 PM', '', ''),
(435, 'd765a559245c0d850db33ac21d9b4807', 'Anuradha', '38.183.25.46', '2024-06-22 14:31:55 PM', '', ''),
(436, 'f4a47eecbb0a85a00f46aa214c30e918', 'iqbal', '38.183.25.46', '2024-06-22 16:42:19 PM', '', ''),
(437, '871987c25e682993eb9c7aa36cfad02b', 'Anuradha', '38.183.50.7', '2024-06-24 09:07:04 AM', '', ''),
(438, '94a14babe88234698b411482a758bb25', 'Anuradha', '38.183.50.7', '2024-06-25 09:27:59 AM', '', ''),
(439, '49857ecf7ef61f19f1c3263a7481082d', 'dvepl', '59.99.152.6', '2024-06-26 11:41:57 AM', '', ''),
(440, '93b96e8d3c2866bd3b5ea1ececce084f', 'Anuradha', '59.99.152.6', '2024-06-27 14:58:23 PM', '', ''),
(441, '0f4d751b7caf347201d33dcc9747021e', 'iqbal', '38.183.31.229', '2024-06-27 17:16:55 PM', '', ''),
(442, '95d364e064361b84f339f3e0b394d019', 'Anuradha', '38.183.31.229', '2024-06-28 10:25:44 AM', '', ''),
(443, '5ce1a22703e811b1d2c468d9d82b9652', 'Anuradha', '38.183.14.88', '2024-06-28 11:54:27 AM', '', ''),
(444, 'a955f451e1d192fa82657bfca9976d76', 'iqbal', '38.183.14.88', '2024-06-28 14:04:22 PM', '', ''),
(445, '172223c926b10200eda403767b644cbc', 'dvepl', '59.99.152.6', '2024-06-28 16:35:34 PM', '', ''),
(446, '1b4ef34a28de7d8df4e6b2becaa17d15', 'iqbal', '38.183.14.88', '2024-06-29 09:51:13 AM', '', ''),
(447, '21eb0b1c685fd1d643a67ac5ab61492f', 'Anuradha', '38.183.22.96', '2024-06-29 12:43:06 PM', '', ''),
(448, '4028b5fda5ccf7633a126f11867528cf', 'iqbal', '38.183.22.96', '2024-06-29 13:51:46 PM', '', ''),
(449, '34f82f1d6171d6289b808904fb6a7c1b', 'Anuradha', '38.183.39.24', '2024-07-01 09:15:08 AM', '', ''),
(450, '201e2199fa29eee0cfc2c58410e33b19', 'dvepl', '59.99.152.6', '2024-07-01 10:24:19 AM', '', ''),
(451, '5319631550d0960cb45e22ba5a67a584', 'iqbal', '38.183.39.24', '2024-07-01 12:05:02 PM', '', ''),
(452, '045af21d18403ebab0140bda83cdaa51', 'Anuradha', '38.183.30.3', '2024-07-02 12:11:25 PM', '', ''),
(453, '9637b9ce8305ed0d8b4222318a852430', 'dvepl', '2409:4055:607:2efa:f1ea:c230:2dc9:8e5a', '2024-07-02 18:49:17 PM', '', ''),
(454, 'e4f16b5196b56ba99db4dbc9f6c0dbbc', '', '59.99.152.6', '2024-07-02 18:59:16 PM', '', ''),
(455, '067e0f9dd2dfe53096254e7be31d39fb', 'dvepl', '59.99.152.6', '2024-07-02 19:01:41 PM', '', ''),
(456, 'e60c9fab3a84073188e6edb7cbc52e7d', '', '59.99.152.6', '2024-07-02 19:06:11 PM', '', ''),
(457, '1d07b024e7b825df10a8ab1b47af754c', 'dvepl', '223.178.210.239', '2024-07-02 19:12:54 PM', '', ''),
(458, '3fc8941189cfb1596802803733f9220c', 'dvepl', '59.99.152.6', '2024-07-02 19:14:10 PM', '', ''),
(459, '9f972cb15b4025adc2df13314663f57a', '', '59.99.152.6', '2024-07-02 19:19:43 PM', '', ''),
(460, 'fa40205dc3f2d0ec6af50e683578df5d', 'dvepl', '59.99.152.6', '2024-07-02 19:20:20 PM', '', ''),
(461, 'd7a8944ae5c07d7d11fbf3fe5d7b8ff0', 'Anuradha', '59.99.152.6', '2024-07-03 09:52:55 AM', '', ''),
(462, 'd7a8944ae5c07d7d11fbf3fe5d7b8ff0', 'Anuradha', '59.99.152.6', '2024-07-03 09:52:55 AM', '', ''),
(463, '878f2b759a047aa6be0eecd3c1251226', 'iqbal', '38.183.26.65', '2024-07-03 10:53:33 AM', '', ''),
(464, '3094a5f00c9712cfa859934a0533134f', 'dvepl', '59.99.152.6', '2024-07-03 12:37:27 PM', '', ''),
(465, '78f0916dcdde9b99248b7fc7abfc644d', 'Arun', '59.99.152.6', '2024-07-03 12:40:24 PM', '', ''),
(466, 'e988b4f7751c827d2899caf0eb43a8f4', 'dvepl', '38.183.26.65', '2024-07-03 12:47:51 PM', '', ''),
(467, '6fb983308f541917ac345e8a5b09ed4d', 'dvepl', '38.183.26.65', '2024-07-03 12:49:50 PM', '', ''),
(468, '777b79ca73633ebf7c65bf1124e1f996', 'iqbal', '38.183.35.188', '2024-07-03 16:38:40 PM', '', ''),
(469, 'a21b70be693a6fa80bd0a18ff08cd117', 'Anuradha', '38.183.35.188', '2024-07-03 17:16:17 PM', '', ''),
(470, '7a1dac4f09de348a97e51f8f2efeb7cc', 'dvepl', '38.183.35.188', '2024-07-03 18:15:16 PM', '', ''),
(471, '617ed136af7f992c1599957700d6a4e7', '', '38.183.35.188', '2024-07-03 18:16:50 PM', '', ''),
(472, 'b80a8e279f402e0277d8b1fa8c8207ee', '', '38.183.35.188', '2024-07-03 18:20:06 PM', '', ''),
(473, '1c1b06fc9e3c610f3681bcbbeaf5a2a6', 'dvepl', '38.183.35.188', '2024-07-03 18:32:02 PM', '', ''),
(474, '2bcfb521bceef370eab34d92d5e6f786', 'Arun', '157.39.65.92', '2024-07-03 18:42:29 PM', '', ''),
(475, '965a410f46731721ca19fdb660c8a957', '', '2401:4900:1c35:306d:1d75:f07a:a052:d740', '2024-07-03 23:27:59 PM', '', ''),
(476, '8c75fbe2301a137aab8ec5201ffa2a06', 'Anuradha', '38.183.47.97', '2024-07-04 09:17:55 AM', '', ''),
(477, '81f914a2f20e6fb8e540d1176a4fcc34', 'iqbal', '38.183.47.97', '2024-07-04 09:34:37 AM', '', ''),
(478, '28f3bc3c64469e65fe9c81ac19b2daca', 'iqbal', '38.183.47.97', '2024-07-04 10:06:55 AM', '', ''),
(479, 'dfe0089f77cea04c179faa95d79aaa20', '', '38.183.47.97', '2024-07-04 10:25:11 AM', '', ''),
(480, 'b8ed4e633b1f8f652983859e9e79f024', '', '59.99.152.6', '2024-07-04 12:29:33 PM', '', '');
INSERT INTO `user_logs` (`id`, `user_id`, `username`, `user_ip`, `login_time`, `city`, `region`) VALUES
(481, 'ad9996d7939ff184df7f14a2b5f543d6', '', '2409:4055:4e1b:55f6:4194:f04c:e04e:9706', '2024-07-04 13:23:02 PM', '', ''),
(482, '842478d725ce6823ca02e07eeaeae4cc', 'dvepl', '157.39.207.45', '2024-07-04 13:30:54 PM', '', ''),
(483, '13b8755b2567ada7114e47799d05ea97', '', '59.99.152.6', '2024-07-04 13:45:05 PM', '', ''),
(484, '54cc07c1b5718a7f8c1b80863c4c0bcf', '', '38.183.47.97', '2024-07-04 14:52:47 PM', '', ''),
(485, 'd3daa5fb848811fd00cff71813a03397', 'Anuradha', '38.183.47.97', '2024-07-04 16:02:15 PM', '', ''),
(486, '9bf828e6d48b7ca81c1b4e5f17f5a9f8', 'iqbal', '38.183.47.97', '2024-07-04 16:27:27 PM', '', ''),
(487, 'cd09020aaa45303c09690d58256144fb', '', '2401:4900:5f3f:cef1:607e:e9a1:27f9:ac0e', '2024-07-04 22:47:31 PM', '', ''),
(488, '4ec2f65d6ca8338a4428cb77af76760e', 'iqbal', '38.183.39.220', '2024-07-05 09:09:21 AM', '', ''),
(489, '482eaf5129b4233be4df6702dea5d13e', 'Anuradha', '38.183.39.220', '2024-07-05 09:12:54 AM', '', ''),
(490, '9d7e39e160a60def54936a07ddc93270', '', '59.99.152.6', '2024-07-05 12:27:07 PM', '', ''),
(491, '0ae34e449018a2baccc84a442ad08041', 'iqbal', '38.183.41.178', '2024-07-05 14:22:43 PM', '', ''),
(492, 'ca6cc6ee513f806cb70285bdba304250', '', '117.210.192.221', '2024-07-06 05:15:24 AM', '', ''),
(493, 'd62eb66f9aaa565c8f1252cb562b6ada', 'dvepl', '117.210.192.221', '2024-07-06 05:19:19 AM', '', ''),
(494, 'f9132e7965b55a3387d8ea02cbdf97a5', 'dvepl', '117.210.192.221', '2024-07-06 05:54:21 AM', '', ''),
(495, 'b6511dc65b50c43191646d9f7e27724e', 'iqbal', '38.183.25.61', '2024-07-06 09:46:16 AM', '', ''),
(496, '733d5c63d9c3abe45dfbc1a4daa9a77c', 'arun', '38.183.25.61', '2024-07-06 09:56:23 AM', '', ''),
(497, 'c6b5fcc99dd2e3f46a5fe41a82224f32', '', '38.183.25.61', '2024-07-06 13:47:48 PM', '', ''),
(498, 'd96a2a85da2a72a474c58b5f9909443e', 'iqbal', '38.183.25.61', '2024-07-06 15:53:16 PM', '', ''),
(499, 'cabe40000a62cad796dc89cf89bd0ea4', '', '152.59.84.115', '2024-07-06 17:28:43 PM', '', ''),
(500, '300b5e0096071aff22170c2af3383689', 'dvepl', '2401:4900:8306:fa48:b912:beaf:208:69ae', '2024-07-06 17:31:58 PM', '', ''),
(501, 'd100c3781a062bb0f4bffbef493fc25c', 'Anuradha', '38.183.39.177', '2024-07-08 09:09:48 AM', '', ''),
(502, 'daa7b93a12f37a09331a69391d721c32', '', '38.183.39.177', '2024-07-08 09:18:17 AM', '', ''),
(503, '3f3f492465bc41347683c6f0337b62d1', 'iqbal', '38.183.39.177', '2024-07-08 09:19:20 AM', '', ''),
(504, 'f40a2942fed2eda573e822ff6d7b369f', 'dvepl', '38.183.39.177', '2024-07-08 10:30:56 AM', '', ''),
(505, '646a270c58f8e42477d0de0d5cad1c4a', 'dvepl', '38.183.39.177', '2024-07-08 18:44:41 PM', '', ''),
(506, '516a4d628390ea7ceb23bcaf96a2f4bf', 'Anuradha', '38.183.39.132', '2024-07-09 09:16:39 AM', '', ''),
(507, '4e074008ad7a59c1c2c82636e8f52f14', 'iqbal', '38.183.39.132', '2024-07-09 10:45:46 AM', '', ''),
(508, '61915f3a70a4f9ab692507c2f49849b1', 'arun', '38.183.39.132', '2024-07-09 10:55:47 AM', '', ''),
(509, '9b71dfddc402df55f74dc01eaa9a4762', '', '2401:4900:85a9:d758:cd60:32c0:548f:51', '2024-07-09 12:43:30 PM', '', ''),
(510, 'a76cf4c645b975ea41a1014b38be867e', 'iqbal', '38.183.32.95', '2024-07-09 14:27:19 PM', '', ''),
(511, 'fae52a75a5c7f5f5978e35e6fb47fb92', 'KAMAL', '38.183.32.95', '2024-07-09 15:40:32 PM', '', ''),
(512, '6948b8fb250c51de9a48f9bcc36761b5', 'kamal', '38.183.32.84', '2024-07-10 09:12:06 AM', '', ''),
(513, 'a38c63cce3295dcff87afe850ed4439b', 'Anuradha', '38.183.32.84', '2024-07-10 09:19:56 AM', '', ''),
(514, 'e6e5cfc6c71bab60b41c5307500fb6dc', 'iqbal', '38.183.32.84', '2024-07-10 12:17:58 PM', '', ''),
(515, '919947a018dfd13b6d40ba22e49fa43a', 'dvepl', '38.183.32.84', '2024-07-10 13:27:47 PM', '', ''),
(516, '5638b51daebb91392a5123c41edc636e', 'dvepl', '38.183.32.84', '2024-07-10 13:30:09 PM', '', ''),
(517, '63b30e3a80d19b0286511f610f0db97e', 'Anuradha', '38.183.32.84', '2024-07-10 13:41:33 PM', '', ''),
(518, '10f299c132c27058f831067d5c17639b', 'kamal', '38.183.32.84', '2024-07-10 13:49:49 PM', '', ''),
(519, '461c2eb1a4bae2404c037eb5e123d5d7', 'Anuradha', '38.183.32.84', '2024-07-10 14:37:17 PM', '', ''),
(520, 'b8f645dd21e07f06d6d9504fc56ed08e', 'iqbal', '38.183.32.84', '2024-07-10 15:06:32 PM', '', ''),
(521, '996595c6f7579ba6ff0138f897d89823', '', '38.183.32.84', '2024-07-10 16:00:03 PM', '', ''),
(522, 'c4d96ee0b8f155994185d0eaf1b5745f', 'iqbal', '38.183.43.188', '2024-07-11 09:15:32 AM', '', ''),
(523, 'bb7779c7749924e7513142418cefa94d', 'Anuradha', '38.183.43.188', '2024-07-11 09:23:36 AM', '', ''),
(524, '1fa3a2f7e2c9b1435cf343686d8f7873', 'Kamal', '38.183.43.188', '2024-07-11 10:08:21 AM', '', ''),
(525, '7907d846bba4c27e8a096cb19a44f301', '', '2401:4900:80ad:108e:99e:f97d:3dea:e583', '2024-07-11 17:37:14 PM', '', ''),
(526, '700f8322fe6950eabf98a19cee36e8bb', '', '2409:4054:2210:60f:1952:e89e:9e80:d90c', '2024-07-11 18:31:44 PM', '', ''),
(527, '800158d3c87b029308a1a834e1d8aab9', 'kamal', '38.183.43.188', '2024-07-12 09:07:10 AM', '', ''),
(528, 'def04b43dd6d28bf45caaccb90b66ef4', 'iqbal', '38.183.43.188', '2024-07-12 10:05:24 AM', '', ''),
(529, 'cb1ba5b647f9c116d26c2e67d24f6ae7', 'Anuradha', '38.183.43.188', '2024-07-12 10:23:24 AM', '', ''),
(530, '6f3bde44244e43f5fa037a3c216110f7', '', '59.99.152.6', '2024-07-12 10:49:42 AM', '', ''),
(531, '9587eb4649b34d7ff11ebbda35651eb1', 'dvepl', '38.183.43.188', '2024-07-12 12:46:25 PM', '', ''),
(532, '1af669c0c3dd2d168871ef1a5ee9ad8f', 'Anuradha', '38.183.43.188', '2024-07-12 14:40:40 PM', '', ''),
(533, '58f67c0427627a08ef9f26a13d61946c', 'iqbal', '38.183.43.188', '2024-07-12 17:35:46 PM', '', ''),
(534, '52d481049d10e8e97bc26e24622817a7', 'Anuradha', '38.183.25.8', '2024-07-13 09:31:54 AM', '', ''),
(535, '58e88ea48348692e68dd610727a94a3a', 'iqbal', '38.183.25.8', '2024-07-13 10:04:05 AM', '', ''),
(536, '5f10aca76b2470647c5e8da49d3c27df', 'KAMAL', '38.183.25.8', '2024-07-13 13:43:43 PM', '', ''),
(537, '71349dc6916e2bb051626aa31afb6234', 'kamal', '38.183.43.77', '2024-07-15 09:08:42 AM', '', ''),
(538, '651713c9e32a07b76fb983663de6c288', 'Anuradha', '38.183.43.77', '2024-07-15 09:32:12 AM', '', ''),
(539, 'd2854c7a52e6d82f7259f20648b2fb83', 'dvepl', '38.183.43.77', '2024-07-15 10:34:33 AM', '', ''),
(540, '9edb69d403b40b27d23f7eb0818d05e9', 'iqbal', '38.183.43.77', '2024-07-15 10:48:54 AM', '', ''),
(541, 'a27aa49ea91b91a83bcada5be5d56257', '', '59.99.152.6', '2024-07-15 10:50:01 AM', '', ''),
(542, '94c4b65aae2308638382897070868dc8', 'dvepl', '38.183.43.77', '2024-07-15 13:08:48 PM', '', ''),
(543, '1323ef12259a1e890a31a0fbe1317ed6', 'iqbal', '38.183.43.77', '2024-07-15 15:49:15 PM', '', ''),
(544, '2d616eb49714ab2c21c9b6331083113a', '', '59.99.152.6', '2024-07-15 16:25:16 PM', '', ''),
(545, 'ceed31864dcb389f53b2a9a2b9fe5151', 'iqbal', '38.183.43.77', '2024-07-16 09:15:23 AM', '', ''),
(546, '8210c6b7f0fa4d05043220d3c9b1d733', 'iqbal', '38.183.43.77', '2024-07-16 09:16:01 AM', '', ''),
(547, '8e3dba46d9f6eefe1ed214c6c5ddf568', 'Kamal', '38.183.43.77', '2024-07-16 09:19:27 AM', '', ''),
(548, '24307cc73fb098f22ca541f3fed00400', 'dvepl', '59.99.152.6', '2024-07-16 09:46:27 AM', '', ''),
(549, '7fc3264acc6c769630a2820b1a7a33ef', 'arun', '38.183.43.77', '2024-07-16 09:52:14 AM', '', ''),
(550, '28eac465a423d96c2b010a5eaa83931c', '', '38.183.43.77', '2024-07-16 10:00:35 AM', '', ''),
(551, '642209d846422134954b3486e08e1b40', 'Sanjay', '59.99.152.6', '2024-07-16 10:20:11 AM', '', ''),
(552, 'cab9ace778c02da3f8946288d79eca0a', 'dvepl', '59.99.152.6', '2024-07-16 10:20:44 AM', '', ''),
(553, 'c902a8bb15e983574db6f890a9ada2ff', 'KAMAL', '38.183.36.1', '2024-07-16 17:48:11 PM', '', ''),
(554, 'fee9f462473e3ef42ef8fc54b6453700', 'dvepl', '59.99.152.6', '2024-07-16 18:46:23 PM', '', ''),
(555, '7efe6f34ccf2fa1883749c47570b000b', '', '27.63.18.115', '2024-07-16 19:43:10 PM', '', ''),
(556, 'be53d92092566bba730124bfe1c2a625', '', '2401:4900:80a7:4dbd:244d:627c:94de:bc17', '2024-07-16 23:34:47 PM', '', ''),
(557, '86d4cc44544c4079e2d25a7b68f8ba99', 'kamal', '38.183.28.24', '2024-07-17 09:30:37 AM', '', ''),
(558, '88408bcfe21dc97bba97c8d243d17a1e', 'dvepl', '38.183.28.24', '2024-07-17 13:15:36 PM', '', ''),
(559, '8cba8f9b8b4c47890cd290025c01091e', 'kamal', '38.183.28.24', '2024-07-17 15:34:57 PM', '', ''),
(560, '3ccc66ff66cd3f002fcc1d503ac5a60a', 'arun', '38.183.28.24', '2024-07-17 15:49:49 PM', '', ''),
(561, 'd985eb53a511b06041c3a779e47723f8', '', '117.98.120.16', '2024-07-17 18:08:08 PM', '', ''),
(562, '684d4c06fd6997130f22cb62192573ef', 'Anuradha', '59.99.152.6', '2024-07-18 09:28:33 AM', '', ''),
(563, '31b9b76230ae06512aecefed80e5fb39', 'kamal', '38.183.19.115', '2024-07-18 09:48:25 AM', '', ''),
(564, 'd7875fd7b707bf811dbc6c201b8bfbf5', 'iqbal', '38.183.19.115', '2024-07-18 09:49:46 AM', '', ''),
(565, '86dff12e50543d2aca55f452d5ef1767', 'iqbal', '38.183.19.115', '2024-07-18 09:50:16 AM', '', ''),
(566, '521e473e2f8ac4d88b13a1bdf4514443', '', '38.183.19.115', '2024-07-18 15:57:44 PM', '', ''),
(567, '33bcf25537c83fa1cfb99fc0bd3eae73', 'Anuradha', '38.183.22.152', '2024-07-18 16:58:13 PM', '', ''),
(568, 'a77a0dbec6c7f385061eb50a5dc6e083', 'Anuradha', '38.183.22.152', '2024-07-19 09:10:27 AM', '', ''),
(569, '64e5baead878d134f3cc8c9652c359e5', 'dvepl', '112.196.35.166', '2024-07-19 10:32:03 AM', '', ''),
(570, 'e2138a73867a207afbd3ffcc7be81ef8', 'iqbal', '38.183.22.152', '2024-07-19 10:33:15 AM', '', ''),
(571, '7f8f73522e8d7ac2d22c292c4374c3dc', 'Anuradha', '59.99.152.6', '2024-07-19 11:45:28 AM', '', ''),
(572, 'ec2a50e0d483f146e424a4da990500ed', 'Anuradha', '38.183.22.152', '2024-07-19 13:34:49 PM', '', ''),
(573, '04a96a96f2df30f3c489e79e38071bbb', 'kamal', '38.183.22.152', '2024-07-19 13:48:30 PM', '', ''),
(574, '0e8f4ce9d22ed2e1a3f3228b000fb46c', 'Anuradha', '38.183.22.152', '2024-07-19 15:05:13 PM', '', ''),
(575, '8c4ebd62e97871d53b0591ffbbc16896', 'dvepl', '2409:40d1:0:e05b:a0b3:e7a:137:c88c', '2024-07-20 08:14:06 AM', '', ''),
(576, '2264d640b0e4beac4f9b8b639fc493eb', 'dvepl', '2409:40d1:0:e05b:a0b3:e7a:137:c88c', '2024-07-20 08:16:39 AM', '', ''),
(577, 'b1f40e5d316324d683cd4bf15502bdf1', 'Anuradha', '59.99.152.6', '2024-07-20 15:59:22 PM', '', ''),
(578, '3841e2769d40ef39a40a2c2aeeae6139', 'KAMAL', '38.183.30.117', '2024-07-20 16:02:37 PM', '', ''),
(579, '3841e2769d40ef39a40a2c2aeeae6139', 'KAMAL', '38.183.30.117', '2024-07-20 16:02:38 PM', '', ''),
(580, '9ae10479cddf66c8325f5cd8fa301749', 'iqbal', '38.183.30.117', '2024-07-20 16:47:01 PM', '', ''),
(581, 'c9f07c044a1413c3dc2f3869abf1e93f', 'kamal', '38.183.30.117', '2024-07-20 17:31:52 PM', '', ''),
(582, 'd05c054f219b9aca4f5453c9545bbb43', 'dvepl', '2401:4900:8141:4b75:45c0:d71b:c8e9:dace', '2024-07-21 19:01:47 PM', '', ''),
(583, '717b7a7ff0340a3a6205b31340e5c161', '', '223.178.208.47', '2024-07-22 00:23:55 AM', '', ''),
(584, 'cd3fe8d0a7841069aef6a134ed63248e', '', '223.178.208.47', '2024-07-22 00:29:50 AM', '', ''),
(585, 'edd743634a7bffa9a3c49fd355a4edd5', '', '223.178.208.47', '2024-07-22 00:33:05 AM', '', ''),
(586, '8f31e81ba3ecc234335f5580a6fd4eb2', 'dvepl', '223.178.208.47', '2024-07-22 00:53:52 AM', '', ''),
(587, 'aac2035a06c5887b5edaca5a36c37ea0', '', '223.178.208.47', '2024-07-22 02:21:08 AM', '', ''),
(588, 'c7ccaefdb17def11d4988ce8bf08ca94', 'dvepl', '223.178.208.47', '2024-07-22 02:21:42 AM', '', ''),
(589, '698230696ab230cecff3c061668ad700', '', '223.178.208.47', '2024-07-22 02:46:14 AM', '', ''),
(590, '586557bfd9791c1621ee8fd5b0ebdb1e', 'dvepl', '223.178.208.47', '2024-07-22 02:46:49 AM', '', ''),
(591, '8b51e99561c03d896a5c1d5c6bdb374f', 'Anuradha', '59.99.152.6', '2024-07-22 09:14:21 AM', '', ''),
(592, '3b0b9b34c43ce3721030257ab48718ce', 'iqbal', '38.183.15.160', '2024-07-22 09:25:19 AM', '', ''),
(593, '3fd21c0ce8d169311e336dd55a40db2a', 'KAMAL', '38.183.15.160', '2024-07-22 10:56:48 AM', '', ''),
(594, '5f201ae5a4e79a6d78fba58bd479d09e', 'iqbal', '38.183.15.160', '2024-07-22 11:10:27 AM', '', ''),
(595, '60ff42714ff25d90f3334b2a8ba3607c', 'dvepl', '223.178.208.195', '2024-07-22 11:48:27 AM', '', ''),
(596, '7f26b12a7b73b72408d252a028409c65', '', '59.99.152.6', '2024-07-22 11:54:43 AM', '', ''),
(597, 'od7fot09kvmjqinu5b1luqvdno', 'dvepl', '::1', '2024-07-22 13:22:50 PM', '', ''),
(598, '0q0km8358462qqkgl6bqbjce9q', 'dvepl', '::1', '2024-07-22 16:23:08 PM', '', ''),
(599, 'bh6nv4lmitql2foecp655i8dec', '', '::1', '2024-07-22 17:16:27 PM', '', ''),
(600, 'nbk0305etdhnei2233atb59015', 'dvepl', '::1', '2024-07-22 17:35:05 PM', '', ''),
(601, 'rtur93fqn55fds13vq43lgvqqc', 'dvepl', '::1', '2024-07-23 10:03:26 AM', '', ''),
(602, 'st6rrc7a2f9dphfk3k4e86rr2i', '', '::1', '2024-07-23 11:54:49 AM', '', ''),
(603, '5j3omrvnv14t67olv798dd825l', '', '::1', '2024-07-23 16:20:40 PM', '', ''),
(604, 'n43er3r6pdavg8qcva4f6an664', 'dvepl', '::1', '2024-07-23 17:05:05 PM', '', ''),
(605, 'b0f9uojsjpiomuu58mqj70b0vc', 'dvepl', '::1', '2024-07-23 19:48:14 PM', '', ''),
(606, '638a47vg2r2kt0kg2rk62rlg6c', 'dvepl', '::1', '2024-07-23 20:10:53 PM', '', ''),
(607, 'pr8mbvd8pbo6t23mfecg9gaa7d', 'dvepl', '::1', '2024-07-24 07:49:20 AM', '', ''),
(608, '66c1qei4m9taflcpt3il470vhg', 'dvepl', '::1', '2024-07-24 07:55:54 AM', '', ''),
(609, 'vqb6rcgiaskin03dfghmcl700j', 'dvepl', '::1', '2024-07-24 10:29:33 AM', '', ''),
(610, '82ig6vqblqk7i84maj986c8mbp', 'dvepl', '::1', '2024-07-24 10:30:42 AM', '', ''),
(611, 'snj4ioavcg69jv98731j3m4rus', '', '::1', '2024-07-24 13:14:47 PM', '', ''),
(612, '3g6hkcr4g48uub3cmu6pn1ruso', '', '::1', '2024-07-24 19:47:03 PM', '', ''),
(613, '43m6fpecvnsba9u19ccimhbuqj', '', '::1', '2024-07-25 10:00:01 AM', '', ''),
(614, 'k8l5h14mu267bi4330crj0kkpk', 'dvepl', '::1', '2024-07-25 10:10:38 AM', '', ''),
(615, 'pd29cp6etl9nc1n9b2e5eeprad', '', '::1', '2024-07-25 16:23:47 PM', '', ''),
(616, 'tlnagegnh8pl86p7fqrn15lrac', 'dvepl', '::1', '2024-07-26 10:13:40 AM', '', ''),
(617, 'd8fdk699s34evcr7r0piclu23v', 'dvepl', '::1', '2024-07-26 10:19:06 AM', '', ''),
(618, 'ipd95ld7i9u0up528u8t5dnhsv', '', '::1', '2024-07-26 11:16:50 AM', '', ''),
(619, 'ane7n4efkfulq796squsi4a6qg', '', '::1', '2024-07-26 19:00:24 PM', '', ''),
(620, 'k4om1ohuuki1k2qdkn75c2tev3', 'dvepl', '::1', '2024-07-26 19:55:09 PM', '', ''),
(621, 'h1nv4sa9vqesm2rf98gvhd36sk', 'dvepl', '::1', '2024-07-26 19:57:36 PM', '', ''),
(622, 'ra8vuc076po134dknqcuf3ibo4', '', '::1', '2024-07-26 20:01:01 PM', '', ''),
(623, 'jsogvvtqlm5ja5ukgehlv3hgvt', 'dvepl', '::1', '2024-07-26 20:05:59 PM', '', ''),
(624, '0acbs09djnfvg0s1cs45ka5ijt', 'dvepl', '::1', '2024-07-27 23:00:17 PM', '', ''),
(625, 'hqp52j731iqqssdrteftg3ehg3', 'dvepl', '::1', '2024-07-27 23:05:04 PM', '', ''),
(626, 'bs2u1ddngtljpg0d3uot5vombf', '', '::1', '2024-07-27 23:26:15 PM', '', ''),
(627, '8151qlvovsh3meec51hatkjtma', 'dvepl', '::1', '2024-07-27 23:27:06 PM', '', ''),
(628, 'e3tvmjh5b6mdmvhn089kqttf9k', 'dvepl', '::1', '2024-07-27 23:42:48 PM', '', ''),
(629, 'iguqj6vctp9713gdtia7k98i8k', 'dvepl', '::1', '2024-07-28 11:09:16 AM', '', ''),
(630, 'd2j7bajl4e7toee39esa47737g', 'dvepl', '::1', '2024-07-28 12:18:39 PM', '', ''),
(631, '89s0s1u1ga3q3vr5j1tsjnngbb', '', '::1', '2024-07-28 12:19:03 PM', '', ''),
(632, '4cmgvhqbo3qgbkv6nlgnobfmqq', 'dvepl', '::1', '2024-07-30 22:04:24 PM', '', ''),
(633, 'ndqb0jlgt8em1vr27gcdejnnh0', 'dvepl', '::1', '2024-07-31 11:09:38 AM', '', ''),
(634, 'cfl4q7gpkb67imabql5gcluu77', 'dvepl', '::1', '2024-07-31 11:16:45 AM', '', ''),
(635, 'btudopleppu201j4re9thtmst5', 'dvepl', '::1', '2024-07-31 12:11:37 PM', '', ''),
(636, '7v0gub8grbjk5pijgu0sg6spls', 'dvepl', '::1', '2024-07-31 12:16:25 PM', '', ''),
(637, 'nq7dnu5j54pfule2ne3khsqbvi', '', '::1', '2024-07-31 13:18:45 PM', '', ''),
(638, 'tgccaetgs714n8odcbuq9sn335', '', '::1', '2024-07-31 13:21:54 PM', '', ''),
(639, 'qc5vc39tt71epcah4stvr3bicb', 'dvepl', '::1', '2024-07-31 16:59:46 PM', '', ''),
(640, '44p024b39fj3ashb6q2jog6ejv', 'dvepl', '::1', '2024-07-31 17:07:28 PM', '', ''),
(641, 'rmb7g69qk7rtu3plqil3cir1lm', 'dvepl', '::1', '2024-07-31 18:43:05 PM', '', ''),
(642, '1gdhffs3v6jfinkhj1oogvtq3e', 'dvepl', '::1', '2024-07-31 18:45:02 PM', '', ''),
(643, '82jvribgu14ectt0bl11vslocs', 'dvepl', '::1', '2024-08-01 10:17:02 AM', '', ''),
(644, 'revkqhb3sghjf7s75oobcnhoc9', 'dvepl', '::1', '2024-08-01 10:27:05 AM', '', ''),
(645, 'kfkgcs25ojhnsp3er8t89t7o4o', 'dvepl', '::1', '2024-08-01 10:56:23 AM', '', ''),
(646, '586pfluhv6dt4bb6gau0cbm9bq', 'dvepl', '::1', '2024-08-01 10:56:40 AM', '', ''),
(647, 'h2odc4ch8lco3tf1c80h1j1p4m', '', '::1', '2024-08-01 10:57:57 AM', '', ''),
(648, '6mp6595m8uko7mi3nic79db1qr', 'dvepl', '::1', '2024-08-01 11:03:57 AM', '', ''),
(649, 'idbcmmmle1nj4cnphd51ttm8hg', 'dvepl', '::1', '2024-08-01 11:16:54 AM', '', ''),
(650, 'tpmvorsrlgj371chn17371h5ed', 'dvepl', '::1', '2024-08-01 11:17:34 AM', '', ''),
(651, 'qaqtqa15l2u0d23opj9vlhfov9', 'dvepl', '::1', '2024-08-01 11:20:36 AM', '', ''),
(652, 'ltqr7fm1mudef7n4kvii8kmfl6', 'dvepl', '::1', '2024-08-01 11:20:50 AM', '', ''),
(653, 'j6qpa50u494kn0jodkt5mmdpi4', 'dvepl', '::1', '2024-08-01 11:22:01 AM', '', ''),
(654, 'j1d81v4nfrqm3f3cbs97ke9imi', 'dvepl', '::1', '2024-08-01 11:22:23 AM', '', ''),
(655, 'k6st5874p5902n54bfh7hj5pjf', '', '::1', '2024-08-01 11:25:08 AM', '', ''),
(656, '3b4bg6bij97r8r8lh63nal9022', 'dvepl', '::1', '2024-08-01 11:25:17 AM', '', ''),
(657, '9qnr2n85ksfmqc0qgv3mpmmupv', 'dvepl', '::1', '2024-08-01 11:26:48 AM', '', ''),
(658, 'bjcbg4ctmf9eogsgp7f64et48v', 'dvepl', '::1', '2024-08-01 11:27:08 AM', '', ''),
(659, 'ff9ssv47cdge9nvs783umlunku', 'dvepl', '::1', '2024-08-02 09:59:46 AM', '', ''),
(660, '1dngm0v2i05k6bpo4osvpmtsgl', 'dvepl', '::1', '2024-08-02 12:53:01 PM', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_tender_requests`
--

CREATE TABLE `user_tender_requests` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `tenderID` varchar(20) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(15) NOT NULL,
  `tender_no` varchar(20) DEFAULT NULL,
  `name_of_work` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `reference_code` varchar(20) DEFAULT NULL,
  `section_id` varchar(20) NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `selected_user_id` int(11) DEFAULT NULL,
  `allotted_at` datetime DEFAULT NULL,
  `reminder_days` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `division_id` varchar(11) DEFAULT NULL,
  `sub_division_id` varchar(11) DEFAULT NULL,
  `due_date` varchar(30) NOT NULL,
  `remark` varchar(10) DEFAULT NULL,
  `remarked_at` datetime DEFAULT NULL,
  `project_status` varchar(11) DEFAULT NULL,
  `file_name2` varchar(255) DEFAULT NULL,
  `tentative_cost` varchar(200) DEFAULT NULL,
  `auto_quotation` varchar(255) DEFAULT '0',
  `delete_tender` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_tender_requests`
--

INSERT INTO `user_tender_requests` (`id`, `member_id`, `tenderID`, `department_id`, `created_at`, `status`, `tender_no`, `name_of_work`, `file_name`, `reference_code`, `section_id`, `sent_at`, `selected_user_id`, `allotted_at`, `reminder_days`, `updated_at`, `division_id`, `sub_division_id`, `due_date`, `remark`, `remarked_at`, `project_status`, `file_name2`, `tentative_cost`, `auto_quotation`, `delete_tender`) VALUES
(30, 32, '2023_MES_619989_2', 41, '2023-11-09 08:19:08', 'Allotted', '1234', 'CAA', '65d7275a9027d_H_7700064.pdf', 'CA', '62', '2024-02-22 16:22:10', 90, '2024-02-22 16:34:45', 13, '2024-07-26 11:13:55', '62', '122', '2023-11-15', NULL, NULL, NULL, '', '12', '1', '0'),
(62, 24, 'parkash electric co', 39, '2023-11-14 07:10:06', 'Requested', NULL, NULL, '65531d4ea675a_MAIN LT PANEL Model (1).pdf', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2023-11-16', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(68, 32, '2022_MES_540434_1', 41, '2023-11-15 06:58:57', 'Allotted', '51/CWE/U(U)/2022-23', 'PROVN OF INSTALLATION OF 01 X TRANSFORMER OF 400 KVA WITH PANEL BOARD SUPPORTING MCBS WITH UNDERGROUND WIRING AT AFVH UNDER GE (UTILITIES) UDHAMPUR', '655b15ff25ebc_DVEPL- OFFER 51-CWE-U-(U)2022-23.pdf', '5202', '54', '2023-11-20 13:47:03', 41, '2023-11-20 14:25:23', 15, '2024-07-26 11:13:55', '27', '104', '2022-08-17', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(70, 32, '2022_MES_538640_1', 41, '2023-11-15 07:00:37', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2022-11-17', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(72, 32, '2022_MES_564917_1', 41, '2023-11-15 07:02:42', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2022-12-20', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(74, 32, '2022_MES_560757_1', 41, '2023-11-15 07:04:21', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2022-11-26', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(75, 32, '2022_PDD_195404_1', 41, '2023-11-15 07:05:14', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2022-12-17', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(79, 32, '2022_MES_566810_4', 41, '2023-11-15 07:18:58', 'Sent', '59 CWE/JP-/2022-23', 'PROVN OF TRANSFORMERS 500 KVA TROLLEY MOUNTED WITH CONNECTED ITEMS AT MIL STATION RATNUCHAK UNDER GE KALUCHAK', '655afb81702d6_DVEPL- OFFER 59 CWE-JP-2022-23...5277.pdf', '5277', '59', '2023-11-20 11:54:01', NULL, NULL, NULL, '2024-07-25 18:48:07', '46', '80', '2023-08-21', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(82, 32, '2022_MES_559251_1', 41, '2023-11-15 08:20:35', 'Allotted', 'CEBZ/MRT/T-14 OF 202', 'AUGMENTATION & IMPROVEMENT OF EXISTING ELECTRICITY INFRA AT MEERUT', '667fec0d9c39d_QUOTATION FOR CEBZ-MRTT-14 - 2022-23   5285.pdf', '5285', '68', '2024-06-29 16:42:13', 209, '2024-07-02 14:49:44', 15, '2024-07-26 11:13:55', '97', '200', '2022-12-30', NULL, NULL, NULL, '', '7202000', '1', '0'),
(84, 32, '2022_MES_570260_1', 41, '2023-11-15 08:22:06', 'Allotted', 'CEPZ-22 / 2022-23', 'IMPROVEMENT/UPGRADATION IN ELECTRIC SUPPLY DISTRIBUTION SYSTEM AND CONNECTED SWITCH GEARS UNDER GE (SOUTH) PATHANKOT', '667ff361e2b22_Offer Report- CEPZ 22- 22-23 - 22-Jan-2023 06 11 31 PM.pdf', '5307', '59', '2024-06-29 17:13:29', 204, '2024-07-02 14:25:17', 15, '2024-07-26 11:13:55', '48', '86', '2023-01-27', NULL, NULL, NULL, '', '334100', '1', '0'),
(85, 32, '2022_MES_572131_1', 41, '2023-11-15 08:22:57', 'Sent', 'CWE/ASR-83/2022-23', 'CERTAIN REPAIR TO SAFETY EQPTS AND ALLIED WORKS AT ELECT SUB STATION AT NAMS', '65d887f2772d4_DVEPL- OFFER 83-CWE-ASR-2022-23.pdf', '5316', '58', '2024-02-23 17:26:34', NULL, NULL, NULL, '2024-07-25 18:48:07', '43', '69', '2023-01-18', NULL, NULL, NULL, '', '3051000', '1', '0'),
(86, 32, '2022_MES_570471_1', 41, '2023-11-15 08:23:35', 'Allotted', 'CEPZ-19 / 2022-23', 'PROVN OF ALT EXIT ROAD NEAR GD POST NO 3 AT BIRPUR RATNUCHAK', '667ff2ad833fb_DVEPL- OFFER 19-CEPZ-2022-23.pdf', '5317', '59', '2024-06-29 17:10:29', 205, '2024-07-02 14:27:39', 15, '2024-07-26 11:13:55', '', '', '2023-01-27', NULL, NULL, NULL, '', '110500', '1', '0'),
(87, 32, '2022_MES_566009_5', 41, '2023-11-15 08:24:12', 'Allotted', 'CWE/P-53/2022-23', 'PROVN OF EXTERNAL ELECTRIC SUPPLY 33 11KV SUB STN AND ALLIED SERVICES FOR OTM TANDA PHASE II FOR WSI SQN AT TANDA UNDER GE PROJECT TANDA', '667ff0c889fe8_QUOTATION 5309.pdf', '5390', '54', '2024-06-29 17:02:24', 207, '2024-07-02 14:42:09', 15, '2024-07-26 11:13:55', '23', '', '2023-01-10', NULL, NULL, NULL, '', '168000', '1', '0'),
(89, 32, '2022_MES_523426_3', 41, '2023-11-15 08:28:18', 'Allotted', 'CWE/JAL/E-08/2022-23', 'Comprehensive repair and maintenance of lifts at MH under GE (East) Jalandhar Cantt.', '65d889abaf56b_DVEPL- OFFER 8-CWE-JAL-E-2022-23...5330.pdf', '5330', '58', '2024-02-23 17:33:55', 165, '2024-06-19 15:14:15', 15, '2024-07-26 11:13:55', '45', '75', '2022-10-03', NULL, NULL, NULL, '', '108000', '1', '0'),
(90, 32, '2022_MES_571924_1', 41, '2023-11-15 08:28:57', '', 'CWE/ASR-81/2022-23', 'CERTAIN REPAIR / REPLACEMENT TO WATER SUPPLY NETWORK INSIDE OTM, MD ACCN, WS INSTLNS AND ALLIED WKS AT NAMS', '667ff145b2dd6_QUOTATION FOR PANELS  81-CWE-ASR-2022-23.pdf', '5331', '58', '2024-06-29 17:04:29', NULL, NULL, NULL, '2024-07-21 19:36:49', '43', '71', '2023-01-24', NULL, NULL, NULL, '', '1655000', '', '0'),
(92, 32, '2023_MES_573846_2', 41, '2023-11-15 08:31:06', 'Allotted', 'CELZ-2022-23', 'PROVN OF CENTRALIZE HEATING SYSTEM (CHS) IN KLP ACCOMMODATION OF 22 WEU AT LEH', '655c9e63261fb_DVEPL- OFFER CELZ-2022-23  5338.pdf', '5338', '53', '2023-11-21 17:41:15', 89, '2024-02-06 01:15:26', 15, '2024-07-26 11:13:55', '21', '18', '2023-07-06', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(93, 32, '2023_MES_572634_1', 41, '2023-11-15 08:32:25', 'Allotted', '18/CE RND/DLI-/CHAND', 'REPLACEMENT OF CENTRAL AC PLANTS AGAINST BER AT MPO AND TTD BLDG AND REPLACEMENT OF SUMMER APPLIANCES AGAINST BER ITEMS FOR THE YEAR 2014-15 AND 2015-16 AT ITR CHANDIPUR', '655afccf62450_DVEPL- OFFER 18-CE RNDDLI-CHANDI-2022-2...5353.pdf', '5353', '60', '2023-11-20 11:59:35', 38, '2023-11-20 12:50:10', 15, '2024-07-26 11:13:55', '54', '95', '2023-07-02', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(94, 32, '2022_MES_561794_1', 41, '2023-11-15 08:34:32', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2023-02-25', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(97, 32, '2022_MES_555422_3', 41, '2023-11-15 08:37:28', 'Allotted', 'CESZ- /2022-23', 'UPGRADATION OF AVN INFRA (PHASE-I) AT SHARIFABAD (DESIGN AND BUILD)', '668002da7c439_DVEPL- OFFER CESZ- 2022-23...5375.pdf', '5375', '51', '2024-06-29 18:19:30', 202, '2024-07-02 12:26:08', 15, '2024-07-26 11:13:55', '', '', '2023-02-13', NULL, NULL, NULL, '', '355000', '1', '0'),
(114, 32, '2023_MES_577179_1', 41, '2023-11-15 09:28:23', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2023-02-24', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(119, 32, '2023_MES_572936_1', 41, '2023-11-15 09:34:32', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2023-02-25', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(122, 32, '2022_MES_554329_4', 41, '2023-11-16 05:53:07', 'Sent', 'CWE/ASR-37/2022-23', 'SPECIAL REPAIR / REPLACEMENT OF LAUNDRY PLANT AGAINST BER TO MH AT AMRITSAR CANTT UNDER GE AMRITSAR', '667fe5b5539d2_QUOTATION FOR 37-CWE-ASR-2022-23.pdf', '5400', '58', '2024-06-29 16:15:09', NULL, NULL, NULL, '2024-07-25 18:48:07', '43', '69', '2022-12-28', NULL, NULL, NULL, '', '260000', '1', '0'),
(126, 32, '2022_MES_567646_2', 41, '2023-11-16 06:16:51', 'Allotted', '24/CEWAC/AMB/T/2022-', 'DEMOLITION AND RECONSTRUCTION OF MD ACCN AT RACE COURSE AT AF STN AMBALA', '655b1500da8b8_DVEPL- OFFER 24-CEWAC-AMB-T-2022-23.pdf', '5411', '55', '2023-11-20 13:42:48', 128, '2024-06-14 15:44:02', 15, '2024-07-26 11:13:55', '34', '45', '2023-07-21', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(131, 32, '2022_MES_545215_1', 41, '2023-11-16 06:22:09', 'Allotted', 'CEJZ/ASR-2022-23', 'REHABILITATION OF EXISTING RUNWAY AND RELATED WORKS AT KHASA UNDER GE NAMS', '655b20f65afef_R1-QUOTATION FOR PANELS CE-JZ-ASR- 2022-23 5435.pdf', '5435', '58', '2023-11-20 14:33:50', 142, '2024-06-14 17:43:39', 15, '2024-07-26 11:13:55', '43', '71', '2022-09-30', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(139, 32, '2023_MES_580999_1', 41, '2023-11-16 06:39:34', 'Allotted', 'CE WAC/CHD/T-26/2022', ' PROVN OF CERTAIN MISC WORK AT AF STN CHANDIGARH (PH-I)', '655ddec68198d_DVEPL- OFFER 26-CE-WAC-CHDT2022-23  5456.pdf', '5456', '55', '2023-11-22 16:28:14', 134, '2024-06-14 16:10:10', 15, '2024-07-26 11:13:55', '32', '42', '2023-06-12', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(142, 32, '2023_MES_579821_2', 41, '2023-11-16 06:59:43', 'Allotted', 'CESZ/TEZ(N)/29 OF 20', 'COMPLETION OF INCOMPLETE WORK OF PROVN OF OTM ACCN FOR ENGINEERING REGIMENT AT TEZUR', '65745bcb8c647_OFFER CANO - 29-CESZTEZ(N) 2022-2023.pdf', '5459', '69', '2023-12-09 17:51:31', 183, '2024-06-24 14:34:15', 15, '2024-07-26 11:13:55', '102', '208', '2023-06-03', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(146, 32, '2023_MES_576084_1', 41, '2023-11-16 10:02:26', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2023-02-25', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(148, 32, '2023_MES_579671_1', 41, '2023-11-16 10:08:22', 'Allotted', 'CE BTZ/BKN/TOKEN-28 ', 'CONST OF 20 DU DEFI MD ACCN FOR OFFRS AT BIKANER MIL STATION', '655afbe909fa8_DVEPL- OFFER CE BTZBKNTOKEN- 28 OF 2022-23  5485.pdf', '5485', '61', '2023-11-20 11:55:45', 126, '2024-06-14 15:32:04', 15, '2024-07-26 11:13:55', '59', '112', '2023-10-04', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(158, 32, '2023_MES_587495_1', 41, '2023-11-18 09:31:55', 'Allotted', 'GE/P-106/2022-23', 'Maint and repairs to various external electric supply items LT panel, under ground cable and other allied works at Base Camp under GE Partapur', '656174d703ce0_DVEPL- OFFER 106-GEP-2022-23...5505.pdf', '5505', '53', '2023-11-25 09:45:19', 47, '2023-12-07 14:48:44', 15, '2024-07-26 11:13:55', '22', '21', '2023-05-03', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(160, 32, '2023_MES_587443_1', 41, '2023-11-18 09:33:10', 'Sent', 'CWE/AMB/UTILITY-57 O', 'PROVISION OF 630 KVA TRANSFORMER ALONGWITH CONNECTED ITEMS FOR JCOs OR GOVT MD ACCN AT BANA SINGH LINES UNDER GE (UTILITY) AMBALA CANTT', '655def45ecb67_DVEPL- OFFER 57-CWE-AMB-UTILITY- 2022-23.pdf', '5519', '56', '2023-11-22 17:38:37', NULL, NULL, NULL, '2024-07-25 18:48:07', '35', '49', '2023-05-02', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(163, 32, '2023_MES_593429_1', 41, '2023-11-18 09:41:26', 'Allotted', 'CEAFU-05/2023-24', 'MODIFICATION OF BLAST PENS AT AF STATION SRINAGAR', '655dddd018453_DVEPL- OFFER 5-CEAFU-2023-24  5528.pdf', '5528', '52', '2023-11-22 16:24:08', 135, '2024-06-14 16:18:33', 15, '2024-07-26 11:13:55', '19', '13', '2023-06-12', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(164, 32, '2023_MES_594002_1', 41, '2023-11-18 09:43:06', 'Allotted', 'GE/KC- /2023-2024', 'SPL REPAIR REPLACEMENT OF BER DG SET, VOLTAGE STABLIZER, VT SUBMERSIBLE PUMPS AND OTHET ALLIED WORKS AT KALUCHAK AND RATNUCHAK MIL STN UNDER GE KALUCHAK', '655d9241515b5_R1-DVEPL- OFFER 14-GEKC-NIT-2023-24...5533 WITH 2MM.pdf', '5533', '59', '2023-11-22 11:01:45', 136, '2024-06-14 16:23:40', 15, '2024-07-26 11:13:55', '46', '80', '2023-06-06', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(166, 32, '2023_MES_592402_3', 41, '2023-11-18 09:46:12', 'Allotted', '18/GE(N)/M/2022-23', 'REPAIR/ MAINTENANCE/ REPLACEMENT OF LT PANELS, EARTHINGS, MCCBS, SECURITY LIGHTS AND CONNECTED ITEMS AT D-IB ZONE AND CENTRAL AMENTIES UNDER AGE E/M -I OF GE (N) MAMUN', '655b132253d17_R1-DVEPL- OFFER 18-GE-(N)M2022-23  5538.pdf', '5538', '59', '2023-11-20 13:34:50', 42, '2023-11-20 14:33:47', 15, '2024-07-26 11:13:55', '48', '85', '2023-06-27', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(169, 32, '2023_MES_592507_1', 41, '2023-11-18 09:54:36', 'Allotted', 'GEP-15/2023-24', 'REPAIR MAINTENANCE OF LT INDOOR PANEL OUTDOOR TYPE FEEDER PILLAR BOX AND OTHER CONNECTED WORKS AT ALHILAL MIL STN', '655d92baf29e1_DVEPL- OFFER 15-GEP-2023-24...5550.pdf', '5550', '59', '2023-11-22 11:03:46', 161, '2024-06-19 14:54:02', 15, '2024-07-26 11:13:55', '49', '90', '2023-05-29', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(170, 32, '2023_MES_587531_1', 41, '2023-11-18 09:55:14', 'Allotted', 'CE BTZ/FDK/TOKEN-35 ', 'ADDN/ALTN TO EXISTING HT NETWORK (FOR CONVERSION TO UNDERGROUND SYSTEM) UNDER GE FARIDKOT', '655ddface0868_DVEPL OFFER  5551.pdf', '5551', '61', '2023-11-22 16:32:04', 52, '2023-11-23 10:56:12', 15, '2024-07-26 11:13:55', '61', '118', '2023-06-10', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(175, 32, '2023_MES_595243_1', 41, '2023-11-20 09:48:17', 'Allotted', 'CWE/NGT-10/2023-24', 'SPECIAL REPAIR/REPLACEMENT OF E/M ITEMS UNDER GE NAGROTA', '655d915137d33_Offer Report- 2023_MES_595243_1  Spl repair_replacement of E_M items under GE Nagrota - 12-Jun-2023 04 15 19 PM.pdf', '5564', '54', '2023-11-22 10:57:45', 44, '2023-11-22 14:16:26', 15, '2024-07-26 11:13:55', '56', '100', '2023-06-19', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(177, 32, '2023_MES_587984_1', 41, '2023-11-20 09:55:55', 'Allotted', 'CESZ/LIKA/ OF 2023-2', 'PROVISION OF PRE ENGINEERED HANGAR WITH ANNEXE BUILDING AT LIKABALI MIL STN PH II OF THREE PHASES', '655de0dcd7ff7_DVEPL- OFFER CESZLIKA OF 2023-24...5569.pdf', '5569', '63', '2023-11-22 16:37:08', 77, '2024-01-06 11:25:47', 10, '2024-07-26 11:13:55', '67', '142', '2023-06-05', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(179, 32, '2022_MES_570306_1', 41, '2023-11-20 10:00:30', 'Allotted', '19/CE CHZ/AMB/T-/202', 'DESIGN AND CONSTRUCTION OF 256 DUs for ORs AT AMBALA CANTT ON EPC MODE', '655c362b7044f_DVEPL- OFFER 19-CE CHZ-AMB-T-2022-23   5577.pdf', '5577', '56', '2023-11-21 10:16:35', 43, '2023-11-21 10:21:36', 15, '2024-07-26 11:13:55', '', '', '2023-08-04', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(185, 32, '2023_MES_594203_1', 41, '2023-11-20 10:04:39', 'Allotted', 'CWE No. 2 / PB / of ', 'Provision of HT Electric Supply from AED, Installation of Two 160 KVA, One 100 KVA Transformer at INS Kohassa', '6573f761ee256_FINAL DVEPL- OFFER 2  PB  of 2023 – 2024...5587.pdf', '5587', '64', '2023-12-09 10:43:05', 57, '2023-12-09 11:37:06', 15, '2024-07-26 11:13:55', '69', '147', '2023-06-20', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(187, 32, '2023_MES_596200_2', 41, '2023-11-20 10:06:48', 'Allotted', ' GE/U- /2023-24', 'Special Repair/Replacement of overhead electric cables to underground electric cables at Inf Bn Loc under GE (Utilities) Udhampur.', '655c9cde66213_DVEPL- OFFER DVEPL- OFFER GEU- 2023-24...5589.pdf', '5589', '54', '2023-11-21 17:34:46', 46, '2023-11-22 13:48:21', 15, '2024-07-26 11:13:55', '27', '104', '2023-07-21', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(188, 32, '2023_MES_596195_2', 41, '2023-11-20 10:07:40', 'Allotted', 'GE/U- /2023-24', 'Special repair to LT overhead to underground cables switchgear, panels and other connected Accessories in Bde Office complex, Single Officers Accn, Primary school under GE(Utility) Udhampur', '655c9bb57d540_DVEPL- OFFER  OFFER  GEU- 2023-24...5590.pdf', '5590', '54', '2023-11-21 17:29:49', 45, '2023-11-22 13:46:09', 15, '2024-07-26 11:13:55', '27', '104', '2023-07-21', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(190, 32, '2023_MES_598779_1', 41, '2023-11-20 10:13:54', 'Allotted', 'GEAF/T-30/2023-24', 'REPAIR/REPLACEMENT OF LT UG CABLE, JUNCTION BOX AND EXTERNAL ELECTRIFICATION AT AF STN THOISE UNDER GE (AF) THOISE', '655d8d31833a3_DVEPL- OFFER 30-GE-AF-T-2023-24...5594.pdf', '5594', '52', '2023-11-22 10:40:09', 133, '2024-06-14 16:06:30', 15, '2024-07-26 11:13:55', '18', '12', '2023-06-17', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(192, 32, '2023_MES_584643_1', 41, '2023-11-20 10:14:23', 'Allotted', '31/CWE(U)/D/2022-23', 'SPECIAL REPAIR/ REPLACEMENT OF OLD UNSV ELECTRIC LT PANELS AND CONNECTED WORKS AT 505 ABW AND SPL REPAIR TO REPLACEMENT/ REROUTING OF HT CABLE 11000 VOLTS FROM COD SUB STN TO KABUL LINE MRS UNDER GE (U) ELECT SUPPLY DELHI  CANTT -10', '655c307cd90e1_DVEPL- OFFER 31-CWE-(U)-D-2022-23...5595.pdf', '5595', '57', '2023-11-21 09:52:20', 78, '2024-01-06 11:29:11', 10, '2024-07-26 11:13:55', '42', '66', '2023-07-31', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(193, 32, '2023_MES_595428_1', 41, '2023-11-20 10:14:49', 'Allotted', ' 4/CEPZ/2023-24', ' REPLACEMENT OF OLD VINTAGE CENTRAL AC PLANT WITH NEW AC PLANT, STANDBY DG SET AND CONNECTED ACCESSORIES AT PATHANKOT UNDER GE (SOUTH) PATHANKOT', '655c3200e9d14_DVEPL- OFFER 4-CEPZ-2023-24...5597.pdf', '5597', '59', '2023-11-21 09:58:48', 38, '2023-11-21 10:14:44', 15, '2024-07-26 11:13:55', '48', '86', '2023-07-31', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(194, 32, '2023_MES_601349_4', 41, '2023-11-20 10:16:40', 'Sent', 'CESZ- /2023-24', ' AUGMENTATION OF WATER SUPPLY AT URI', '6573027c14a4f_DVEPL- OFFER CESZ- 2023-24  5598.pdf', '5598', '51', '2023-12-08 17:18:12', NULL, NULL, NULL, '2024-07-25 18:48:07', '15', '6', '2023-10-09', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(195, 32, '2023_MES_599690_1', 41, '2023-11-20 10:17:05', 'Allotted', 'CWE(BHI)/BHI-12/2023', 'PROVISION OF 500 KVA SPARE TRANSFORMER AND IMPROVEMENT OF ASSOCIATED TWO POLE STRUCTURE AT AF STN FEROZEPUR UNDER GE (AF) BHISIANA', '655c9fdb99110_DVEPL- OFFER 12-CWE-(BHI)-BHI-2023-24.pdf', '5600', '55', '2023-11-21 17:47:31', 50, '2023-11-22 14:12:38', 15, '2024-07-26 11:13:55', '30', '37', '2023-06-26', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(196, 32, '2023_MES_597486_1', 41, '2023-11-20 10:17:27', 'Allotted', 'GE/LDH-18/2023-24', 'Repair and Periodical Maint of DG Sets and AMF Panels installed at Baddowal and Moga under GE Ludhiana', '655da33ddde49_DVEPL- OFFER 18-GE-LDH-2023-24...5602.pdf', '5602', '58', '2023-11-22 12:14:13', 55, '2023-12-04 10:47:41', 15, '2024-07-26 11:13:55', '44', '', '2023-06-17', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(199, 32, '2023_MES_601180_1', 41, '2023-11-20 10:18:53', 'Allotted', 'GE/ASR - 18 OF 2023-', 'REPAIR /MAINTENANCE / REPLACEMENT OF STREET LIGHT AND COMPLAINTS OF STREET LIGHTS AT OLD AMRITSAR CANTT', '655d8b45043e9_REVISED DVEPL- OFFER 18-GE-ASR-2023-24...5614.pdf', '5614', '58', '2023-11-22 10:31:57', 48, '2023-11-22 13:59:49', 15, '2024-07-26 11:13:55', '43', '69', '2023-07-03', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(200, 32, '2023_MES_594188_1', 41, '2023-11-20 10:19:43', 'Sent', 'CEUZ/UDH/ /2023-24', 'RECONSTRUCTION OF BAFFLE RANGE AT UDHAMPUR', '655c326c99423_DVEPL- OFFER CEUZ-UDH- 2023-24...5615.pdf', '5615', '54', '2023-11-21 10:00:36', NULL, NULL, NULL, '2024-07-25 18:48:07', '27', '99', '2023-10-14', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(201, 32, '2023_MES_602955_1', 41, '2023-11-20 10:20:51', 'Allotted', '9/ CWEAFJ/2023-24', 'PROVN OF ELECTRICAL INFRASTRUCTURE AND ASSOCIATED WORKS SERVICES FOR OPERATIONALISATION OF IPSS AT AF STN UDHAMPUR', '655c49d43b999_DVEPL- OFFER 9 CWE-AFJ-2023-24 ..5616.pdf', '5616', '52', '2023-11-21 11:40:28', 44, '2023-11-21 12:36:14', 15, '2024-07-26 11:13:55', '17', '9', '2023-07-24', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(202, 32, '2023_MES_601573_1', 41, '2023-11-20 10:21:45', 'Allotted', 'GE/ASR - 22 OF 2023-', 'REPAIR REPLACEMENT OF LT OH LINE/ UG CABLES AND CONNECTED ITEMS OF ELECTRIC SUPPLY IN THE AREA OF AGE B/R-II AT OLD AMRITSAR CANTT', '655d8a21dd874_REVISED DVEPL- OFFER 22-GE-ASR-2023-24...5617.pdf', '5617', '58', '2023-11-22 10:27:05', 180, '2024-06-22 17:11:56', 15, '2024-07-26 11:13:55', '43', '69', '2023-07-03', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(205, 32, '2023_MES_596967_3', 41, '2023-11-20 11:50:12', 'Sent', '13/CWE/NGT/2023-24', 'AUGMENTATION OF WATER SUPPLY PIPE LINE FROM MES MAIN PUMPING STATION TO DUG WELL NO. 08 AND PROVN OF OH/UG TANK UNDER GE NAGROTA', '655c33ccc9cf0_DVEPL- OFFER 13-CWE-NGT-2023-24...5624.pdf', '5624', '54', '2023-11-21 10:06:28', NULL, NULL, NULL, '2024-07-25 18:48:07', '56', '100', '2023-09-27', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(208, 32, '2023_MES_572702_4', 41, '2023-11-20 11:53:55', 'Allotted', 'CELZ - OF 2023 - 24', 'PROVN OF OTM ACCN FOR LADAKH SCOUTS BN AT KARU (PHASE - IV)', '656d527683fa7_DVEPL- OFFER CELZ- OF 2023 - 24   5634.pdf', '5634', '53', '2023-12-04 09:45:50', 131, '2024-06-14 15:58:45', 15, '2024-07-26 11:13:55', '21', '18', '2023-07-03', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(209, 32, '2023_MES_600767_1', 41, '2023-11-20 11:54:22', 'Allotted', 'GE KPT- 2023-24', 'Repair Replacement Maintenance of street security perimeter lights high mast lights at New Mil Stn Kapurthala under GE Kapurthala', '65696f283365b_DVEPL- OFFER GE KPT- 2023-24   5636.pdf', '5636', '58', '2023-12-01 10:59:12', 132, '2024-06-14 16:00:56', 15, '2024-07-26 11:13:55', '45', '78', '2023-07-03', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(210, 32, '2023_MES_601819_1', 41, '2023-11-20 11:54:55', 'Allotted', 'CWE(BHI)/BHI-19/2023', 'CONVERSION OF OVERHEAD (OH) LT LINES TO UNDERGROUND (UG) CABLE AT AF STN FEROZEPUR UNDER GE (AF) BHISIANA', '655c9d549790b_DVEPL- OFFER 19-CWE-(BHI)-BHI-2023-24...5640.pdf', '5640', '55', '2023-11-21 17:36:44', 130, '2024-06-14 15:53:29', 15, '2024-07-26 11:13:55', '30', '37', '2023-07-04', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(223, 32, '2023_MES_603785_1', 41, '2023-11-23 07:09:09', 'Allotted', 'CEUZ/NGT/ /2023-24', 'CONSTRUCTION OF GIRLS HOSTEL AND ALLIED INFRASTRUCTURE FOR GIRL CADETS IN SAINIK SCHOOL NAGROTA UNDER GE NAGROTA', '65656d325a4ae_Offer Report- CONSTRUCTION OF GIRL HOSTEL AND ALLIED INFRASTRUCTURE FOR GIRL CADETS IN SAINIK SCHOOL NGT - 12-Jul-2023 10 29 42 AM.pdf', '5675', '54', '2023-11-28 10:01:46', 59, '2024-07-03 10:50:38', 15, '2024-07-26 11:13:55', '27', '104', '2023-09-25', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(224, 32, '2022_MES_502859_1', 41, '2023-11-23 07:10:48', 'Allotted', 'CEJZ/JRC- /2022-23', 'Construction of Girls Hostel and Allied Infrastructure at Kapurthala', '65d88899e8564_DVEPL- OFFER CEJZJRC- 2022-23...5683.pdf', '5683', '58', '2024-02-23 17:29:21', 143, '2024-06-14 17:46:44', 15, '2024-07-26 11:13:55', '45', '78', '2022-06-14', NULL, NULL, NULL, '', '173000', '1', '0'),
(247, 32, '2023_MES_609914_1', 41, '2023-11-24 10:26:17', 'Allotted', 'CWE/PAT/T-23/2023-24', 'SPECIAL REPAIR ADDITION ALTERATION OF KENDRIYA VIDHYALAYA NO 1 PATIALA CANTT BUILDING AT PATIALA MILITARY STATION UNDER GE NORTH PATIALA', '656173def1f68_R-1 DVEPL- OFFER 23-CWE-PAT-T-2023-24  5737.pdf', '5737', '56', '2023-11-25 09:41:10', 54, '2023-11-29 10:49:57', 10, '2024-07-26 11:13:55', '37', '53', '2023-08-09', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(249, 32, '2023_MES_601508_2', 41, '2023-11-28 07:17:53', 'Allotted', 'CEPZ-05/2023-24', 'PROVN OF OTM ACCN AT MADHOPUR UNDER GE BASOLI', '65656c85e7396_DVEPL- OFFER 5 CEPZ2023-24...5724.pdf', '5724', '59', '2023-11-28 12:47:53', 59, '2023-12-11 12:35:42', 15, '2024-07-26 11:13:55', '48', '84', '2023-10-07', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(262, 32, '2023_MES_609914_1', 41, '2023-11-29 06:39:40', 'Allotted', 'CWE/PAT/T-23/2023-24', 'SPECIAL REPAIR ADDITION ALTERATION OF KENDRIYA VIDHYALAYA NO 1 PATIALA CANTT BUILDING AT PATIALA MILITARY STATION UNDER GE NORTH PATIALA', '656173def1f68_R-1 DVEPL- OFFER 23-CWE-PAT-T-2023-24  5737.pdf', '5737', '56', '2023-11-29 12:09:40', 54, '2023-12-11 12:39:22', 15, '2024-07-26 11:13:55', '37', '53', '2023-08-09', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(270, 32, '2023_MES_610452_1', 41, '2023-11-29 06:50:01', 'Allotted', 'CE BTZ/FDK/TOKEN-03 ', 'PROVN OF KLP (PHASE-I) UNDER GE FARIDKOT (Job No SW/198)', '656702fc32aa0_DVEPL- OFFER 3-BTZ-FDK-TOKEN- 2023-24...5779.pdf', '5779', '61', '2023-11-29 14:53:08', 125, '2024-06-14 15:25:07', 15, '2024-07-26 11:13:55', '61', '118', '2023-11-09', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(276, 32, '2022_MES_566804_1', 41, '2023-11-29 08:17:39', 'Allotted', 'GE/KC-      /2022-20', 'REPAIR/ MAINTENANCE TO MISC E/M EQUIPMENTS AT KALUCHAK MIL STN UNDER GE KALUCHAK', '667fecf5be333_DVEPL- OFFER 56-GE-KC-NIT-2022-23...5800.pdf', '5800', '59', '2024-06-29 16:46:05', 208, '2024-07-02 14:47:07', 15, '2024-07-26 11:13:55', '46', '80', '2022-12-30', NULL, NULL, NULL, '', '102000', '1', '0'),
(312, 32, '2023_MES_618235_1', 41, '2023-11-30 05:42:32', 'Allotted', 'GE(U)/AMB/T-60/2023-', 'Special Repair to LT Panel, Starters And Connected LT Cable At Sewage Installation And Certain Minor Work Pretaining To Summer Appliances At GE (U) Ambala Cantt', '667fd94db7b31_R1-DVEPL- OFFER 60-GE U AMB T  2023-24  5876.pdf', '5876', '56', '2024-06-29 15:22:13', 199, '2024-07-01 18:23:45', 15, '2024-07-26 11:13:55', '37', '53', '2023-10-09', NULL, NULL, NULL, '', '496000', '1', '0'),
(316, 32, '2023_MES_617984_1', 41, '2023-11-30 05:51:50', 'Allotted', 'GE NAMS?60/2023?24', 'SPL REPAIR OF RE?WIRING OF MD ACCN OF ARMD REGT AND OTHER SANCTINED WORKS AT NAMS', '667fda5705d99_DVEPL- OFFER 60-GE-NAMS-2023-24   5888.pdf', '5888', '58', '2024-06-29 15:26:39', 200, '2024-07-01 18:28:02', 15, '2024-07-26 11:13:55', '43', '71', '2023-10-07', NULL, NULL, NULL, '', '120000', '1', '0'),
(321, 32, '2023_MES_613648_1', 41, '2023-11-30 06:56:36', 'Allotted', 'CECG/GOA/ OF 2023-24', 'CONSTRUCTION OF OTM ACCOMMODATION FOR COAST GUARD REGIONAL HEADQUARTERS NW AT SECTOR 18 GANDHINAGAR', '65685cfdddf2d_DVEPL- OFFER CECGGOA OF 2023-24 5898.pdf', '5898', '65', '2023-11-30 15:29:25', 124, '2024-06-14 15:15:48', 15, '2024-07-26 11:13:55', '78', '159', '2023-11-10', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(322, 32, '2023_MES_617581_1', 41, '2023-11-30 06:58:42', 'Allotted', 'CE BTZ/FDK/TOKEN-08 ', 'PROVN OF AREA DRAINAGE SYSTEM UNDER GE FARIDKOT (Job No NA Para 35 Work)', '667fe20617062_DVEPL- OFFER 8-CE BTZFDKTOKEN2023-24  5899.pdf', '5899', '61', '2024-06-29 15:59:26', 52, '2024-07-03 10:52:41', 15, '2024-07-26 11:13:55', '', '', '2023-10-04', NULL, NULL, NULL, '', '62000', '1', '0'),
(323, 32, '2023_PHE_231415_2', 41, '2023-11-30 07:18:39', 'Allotted', 'e-NIT No. 61 of 2023', 'Providing, Installation, Testing and Commissioning of pumping machinery along with Electro Mechanical Components of 07 no works at different (02 No.) Pumping Stations of Jal Shakti PHE Division Poonch', '656d4ed52ff6b_DVEPL- OFFER  5902.pdf', '5902', '66', '2023-12-04 09:30:21', 181, '2024-06-22 17:20:27', 15, '2024-07-26 11:13:55', '82', '166', '2023-12-06', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(324, 32, '2023_MES_614585_2', 41, '2023-11-30 07:22:59', 'Allotted', 'CWE/ASR-31/2023-24', 'PROVN OF BLDGS FOR MIL DENTAL CENTRE TYPE C AT OLD GE AMRITSAR', '6578059473605_DVEPL- OFFER 31-CWE-ASR-2023-24   5905.pdf', '5905', '58', '2023-12-12 12:32:44', 63, '2023-12-27 12:22:40', 10, '2024-07-26 11:13:55', '43', '69', '2023-11-13', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(328, 32, '2021_MES_489444_1', 41, '2023-11-30 08:50:34', 'Allotted', 'GE/JULL/ET-52/2021-2', ' Special repair to bldg. No P-78 & 79 (SIL/DIL patient guest room) of Military Hospital JRC and certain other sanctioned work under GE (East) Jalandhar Cantt ', '65d885e2d26e1_DVEPL- OFFER 52-GE-JULL-ET-2021-22  5911.pdf', '5911', '58', '2024-02-23 17:17:46', 145, '2024-06-14 17:51:46', 15, '2024-07-26 11:13:55', '45', '75', '2021-12-28', NULL, NULL, NULL, '', '83000', '1', '0'),
(334, 32, '2023_MES_622129_1', 41, '2023-11-30 09:19:21', 'Allotted', 'GE AF/AMB-T-49 of 20', 'REPAIR/ MAINT/ REPLACEMENT/ IMPROVEMENT OF EXTERNAL ELECTRIC, LT DISTRIBUTION SYSTEM, CABLES, LT OH LINES AND CONNECTED ALLIED ITEMS IN DOMESTIC AREA AT AF STN AMBALA', '667fd68b9c777_DVEPL- OFFER 49-GE AF-AMB-T-2023-24   5945.pdf', '5945', '55', '2024-06-29 15:10:27', 198, '2024-07-01 17:58:07', 15, '2024-07-26 11:13:55', '', '', '2023-11-08', NULL, NULL, NULL, '', '650200', '1', '0'),
(336, 32, '2023_MES_622562_1', 41, '2023-12-01 05:01:26', 'Allotted', 'GE KPT- /2023-24', 'Repair/Replacement and Maintenance of LT panels, switch gears and allied works at Mil Stn Beas and Jandiala Guru under GE Kapurthala.', '6576d1eb4ac59_DVEPL- OFFER GE KPT- 2023-24  5950.pdf', '5950', '58', '2023-12-11 14:40:03', 61, '2023-12-12 11:29:18', 15, '2024-07-26 11:13:55', '45', '78', '2023-11-09', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(337, 32, '2023_MES_623237_1', 41, '2023-12-01 05:11:37', 'Allotted', 'GER-60/2023-24', 'REPAIR/REPLACEMENT OF LT OH LINES, UG CABLES, EARTHING AND OTHER CONNECTED WORKS AT NORTH ZONE AT NAUSHERA UNDER AGE E/M OF GE 862 EWS', '6576d6599f7ac_DVEPL- OFFER 60-GER-2023-24  5953.pdf', '5953', '54', '2023-12-11 14:58:57', 64, '2023-12-27 12:42:12', 10, '2024-07-26 11:13:55', '57', '102', '2023-11-04', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(339, 32, '2023_MES_622066_1', 41, '2023-12-01 05:32:02', 'Allotted', 'GER-53/2023-24', 'REPAIR/REPLACEMENT OF LT OVERHEAD LINES, UNDERGROUND CABLE, LT PANELS, BUILDING LIGHTNING PROTECTION SYSTEM, EARTHING AND CONNECTED WORKS UNDER AGE E/M OF GE 862 EWS', '6576d51279279_DVEPL- OFFER 53 GER2023-24  5959.pdf', '5959', '54', '2023-12-11 14:53:30', 60, '2023-12-27 12:26:07', 10, '2024-07-26 11:13:55', '57', '102', '2023-11-04', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(340, 32, '2023_MES_573560_1', 41, '2023-12-01 05:32:36', 'Allotted', 'GE KPT- /2022-23', 'Special repair to bldg No P 30 (01 to 04) and P 32 (01 to 04) in JCOs MD Accn of 2 Mech Inf at New Mil Stn Kapurthala under GE Kaputhala', '667ffcdf32ee6_DVEPL- OFFER 5965.pdf', '5965', '58', '2024-06-29 17:53:59', 143, '2024-07-02 12:50:18', 15, '2024-07-26 11:13:55', '45', '78', '2023-02-04', NULL, NULL, NULL, '', '87000', '1', '0'),
(341, 32, '2023_MES_605504_3', 41, '2023-12-01 05:34:06', 'Allotted', ' AGE(I)/DSL-21/2023-', 'REPAIR/REPLACEMENT OF EXISTING UNSERVICEABLE CORRODED WATER SUPPLY PIPE LINE AND OTHER MISC WORKS AT BHARATPUR LINE AT MIL STN DHARAMSHALA', '655af281caddc_Offer Report- 5968.pdf', '5968', '59', '2023-12-01 11:04:06', 123, '2024-06-14 14:47:38', 15, '2024-07-26 11:13:55', '49', '87', '2023-11-16', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(342, 32, '2023_MES_624157_1', 41, '2023-12-02 05:08:20', 'Allotted', 'GE KPT- /2023-24', 'Provn of Security Lights, Special Repair of Bldg No T-86 and Minor work at Beas under GE Kapurthala.', '6592828364900_DVEPL- OFFER GE KPT- 2023-24  5974.pdf', '5974', '58', '2024-01-01 14:44:43', 69, '2024-01-02 15:31:10', 10, '2024-07-26 11:13:55', '45', '78', '2023-11-18', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(343, 32, '2023_MES_625882_1', 41, '2023-12-02 05:09:51', 'Allotted', '138 WE/L-75/2023-24', 'REPAIR AND REPLACEMENT OF UNSERVICEABLE LT SUPPLY NETWORKS,BUSBAR HAMBERS,EARTHINGS AND ALLIED WORKS AT VARIOUS UNITS UNDER AGE E/M-I OF GE 865 EWS', '6576f07d560b3_DVEPL- OFFER 138-WEL-752023-24  5975.pdf', '5975', '53', '2023-12-11 16:50:29', 121, '2024-06-14 14:35:37', 15, '2024-07-26 11:13:55', '22', '20', '2023-11-21', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(344, 32, '2023_MES_622015_1', 41, '2023-12-02 05:10:46', 'Allotted', 'CWEAFJ-20/2023-24', 'SPECIAL REPAIR TO ATC AND ASSOCIATED BUILDINGS AT GE AIR FORCE STATION PATHANKOT', '6578035606101_DVEPL- OFFER 20-CWE-AFJ-2023-24  5981.pdf', '5981', '52', '2023-12-12 12:23:10', 122, '2024-06-14 14:39:13', 15, '2024-07-26 11:13:55', '17', '', '2023-11-18', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(345, 32, '2023_MES_626637_1', 41, '2023-12-02 08:40:07', 'Allotted', 'CWE/JA-20/2023-24', 'PROVN OF TROLLEY MOUNTED TRANSFORMER 400 KVA FOR STATION AND REPLACEMENT OF OLD VINTAGE LT OH LINE INTO UG CABLE AND ALLIED WORKS AT USMAN VIHAR AREA UNDER GE SOUTH AKHNOOR', '6576ed1d6552d_DVEPL- OFFER 20-CWE-JA-2023-24  5988.pdf', '5988', '54', '2023-12-11 16:36:05', 173, '2024-06-21 09:51:36', 15, '2024-07-26 11:13:55', '23', '22', '2023-12-05', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(346, 32, '2023_MES_625369_1', 41, '2023-12-02 08:41:21', 'Allotted', 'CEPZ- 15/2023-24', 'ADDN ALTN TO EXISTING PERIMETER SECURITY WALL ALONG NH 154 UNDER GE S MAMUN', '667f932beb0c0_DVEPL- OFFER - 5989 .pdf', '5989', '59', '2024-06-29 10:22:59', 194, '2024-06-29 12:54:19', 15, '2024-07-26 11:13:55', '47', '83', '2023-12-16', NULL, NULL, NULL, '', '1224750', '1', '0'),
(348, 32, '2023_MES_623690_1', 41, '2023-12-02 10:05:36', 'Allotted', 'CWE/Y-69/2023-24', 'PROVISION OF UNARMED COMBAT (PTKE) TRG SHED AT SFTS BAKLOH', '6576edb44bd16_DVEPL- OFFER 69- CWE-Y-2023-24  5993.pdf', '5993', '59', '2023-12-11 16:38:36', 120, '2024-06-14 14:29:47', 15, '2024-07-26 11:13:55', '49', '88', '2023-11-29', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(353, 32, '2023_MES_626817_1', 41, '2023-12-07 05:22:44', 'Allotted', 'GE GSR-62/2023-2024', 'Spl repair to OH LT line, UG LT cable at certain area of Khetrapal enclave, Spl repair/replacement of LT UG cable and feeder pillars at Tibri Mil Stn', '6572fd6027cba_DVEPL- OFFER 62-GE GSR-2023-24  6000.pdf', '6000', '58', '2023-12-08 16:56:24', 48, '2023-12-27 12:09:06', 15, '2024-07-26 11:13:55', '43', '70', '2023-11-25', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(354, 32, '2023_MES_627666_1', 41, '2023-12-07 05:23:10', 'Allotted', 'GE/PAF-42/2023-24', 'REPAIR /MAINTENANCE OF LT CABLES AND MISC CONNECTED ITEMS AT TECH AREA AT AIR FORCE STATION PATHANKOT', '6572f5b4deae7_DVEPL- OFFER 42-GE-PAF-2023-24   6004.pdf', '6004', '52', '2023-12-08 16:23:40', 174, '2024-06-21 09:54:11', 15, '2024-07-26 11:13:55', '17', '', '2023-11-30', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(355, 32, '2023_MES_623312_1', 41, '2023-12-07 05:24:26', 'Allotted', 'CEUZ/135WE/ /2023-23', 'CONSTRUCTION OF TECHNICAL BULDING AT RAKHMUTHI', '6571b8aa970ed_DVEPL- OFFER CEUZ135WE 2023-24 6007.pdf', '6007', '54', '2023-12-07 17:50:58', 119, '2024-06-14 14:10:26', 15, '2024-07-26 11:13:55', '23', '22', '2023-12-05', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(357, 32, '2023_MES_616838_1', 41, '2023-12-07 05:30:35', 'Allotted', 'GE/JULL/ET-46/2023-2', 'SPL REPAIRS TO E/M INSTALLATION BLDG. NO. P-110 AND P-125 PATH/ HARDSTANDING AND FENCING ETC AT 111 RKT RGT UNIT AREA AND CERTAIN OTHER SANCTIONED WORK UNDER GE (EAST) JALANDHAR CANTT', '6573f3a3ea286_DVEPL- OFFER 46-GE-JULL-ET-2023-24  6016.pdf', '6016', '58', '2023-12-09 10:27:07', 58, '2023-12-09 12:00:46', 15, '2024-07-26 11:13:55', '45', '75', '2023-09-30', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(358, 32, '2023_MES_627000_2', 41, '2023-12-07 05:32:53', 'Allotted', 'GE/PAF-44/2022-23', 'PROVN OF COVERED SHED, PARKINGS AND PATHWAYS AT VARIOUS LOCATIONS AT AF STATION DALHOUSIE UNDER GE (AF) PATHANKOT', '6572f5174d5b7_DVEPL- OFFER 44-GE-PAF-2023-24   6017.pdf', '6017', '52', '2023-12-08 16:21:03', 118, '2024-06-14 14:05:34', 15, '2024-07-26 11:13:55', '17', '', '2023-12-06', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(359, 32, '2023_MES_627684_1', 41, '2023-12-07 05:33:49', 'Allotted', 'GE/PAF-46/2023-24', 'SPECIAL REPAIR TO BLDG NO P 55 AND CERTAIN REVENEW AND MINIOR WORK AT (AF) STATION DALHOUSIE UNDER GE (AF) PATHANKOT', '6572d04d74606_DVEPL- OFFER 46-GE-PAF-2023-24  6019.pdf', '6019', '52', '2023-12-08 13:44:05', 65, '2023-12-27 15:19:54', 10, '2024-07-26 11:13:55', '17', '', '2023-11-30', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(360, 32, '2023_MES_625222_1', 41, '2023-12-07 05:34:14', 'Allotted', 'GER-56/2023-24', 'PROVN OF OH TO UG CABLES FOR VARIOUS UNITS INCLUDING PROVN OF 6X2 TON AC (HOT AND COLD) UNDER AGE E/M OF GE 862 EWS', '6572d14bf0823_DVEPL- OFFER 56-GER-2023-24   6020.pdf', '6020', '54', '2023-12-08 13:48:19', 184, '2024-06-24 15:54:30', 15, '2024-07-26 11:13:55', '57', '102', '2023-11-30', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(361, 32, '2023_MES_624341_1', 41, '2023-12-07 05:34:36', 'Allotted', 'CE BTZ/BTD/TOKEN-10 ', ' PROVN OF KLP ACCN UNDER GE (P) BATHINDA (Job No MWP/SWC-02/2021-22)', '6571b54a7a0aa_DVEPL- OFFER 10-CE BTZBTDTOKEN2023-24   6021.pdf', '6021', '61', '2023-12-07 17:36:34', 117, '2024-06-14 13:59:07', 15, '2024-07-26 11:13:55', '60', '', '2023-12-07', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(363, 32, '2021_MES_463982_1', 41, '2023-12-07 05:51:56', 'Allotted', ' CE [Navy] / VIZ /  ', 'DEMOLITION/RECONSTRUCTION MARRIED ACCOMMODATION (For 112 NUMBERS OF SAILORS) AT SVN COLONY VISAKHAPATNAM', '65d8849e28828_QUOTATION FOR CE -Navy- VIZ  of 2021 – 2022   6023.pdf', '6023', '70', '2024-02-23 17:12:22', 160, '2024-06-19 14:51:16', 15, '2024-07-26 11:13:55', '', '', '2021-09-14', NULL, NULL, NULL, '', '411000', '1', '0'),
(366, 32, '2023_MES_627698_1', 41, '2023-12-07 05:58:42', 'Allotted', 'GE/PAF-48/2023-24', 'REPAIR /MAINTENANCE OF LT NETWORK SYSTEM AND CONNECTED ITEMS AT NO 02 AND 03 AREA AT AIR FORCE STATION PATHANKOT', '6571b5d4c42dd_DVEPL- OFFER 48-GE-PAF-2023-24   6028.pdf', '6028', '52', '2023-12-07 17:38:52', 172, '2024-06-21 09:49:27', 15, '2024-07-26 11:13:55', '17', '', '2023-12-07', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(367, 32, '2023_MES_627203_1', 41, '2023-12-07 05:59:14', 'Allotted', 'CWE/Y-74/2023-24', 'PROVN OF UNDERGROUND CABLING WITH PANELS AT BAKLOH CANTT', '6572d221ae3f5_DVEPL- OFFER 74-CWE-Y-2023-24  6029.pdf', '6029', '59', '2023-12-08 13:51:53', 157, '2024-06-15 11:04:31', 15, '2024-07-26 11:13:55', '49', '88', '2023-12-05', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(368, 32, '2023_MES_629284_1', 41, '2023-12-07 05:59:52', 'Allotted', 'GE863/EWS-T-54 OF 20', 'PROVN OF RETROFIT EMISSION CONTROL DEVICE IN DG SETS AT VARIOUS LOCS, PROVN OF EXTERNAL WATER SUPPLY TO NFS NODE, SPL REPAIR/REPLACEMENT OF LT PANELS AT POWER HOUSE, PROVN OF 01XPATIENT WAITING SHED NEAR BLDG NO T-117 (MI ROOM) AT SECTION HOSP, PROVN', '6572f4981b77d_DVEPL- OFFER GE863EWS-T-54 OF 2023-24  6030.pdf', '6030', '68', '2023-12-08 16:18:56', 169, '2024-06-20 13:39:20', 15, '2024-07-26 11:13:55', '95', '193', '2023-12-14', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(369, 32, '2023_MES_628319_1', 41, '2023-12-07 06:00:31', 'Allotted', 'CWE/PAT/T?45/2023?24', 'SPECIAL REPAIR TO LT DISTRIBUTION NETWORK UNDER AGE(I) AT DAPPAR MIL STN', '6572cf7678156_DVEPL- OFFER 45CWEPATT-2023-24  6031.pdf', '6031', '56', '2023-12-08 13:40:30', 220, '2024-07-02 17:14:41', 15, '2024-07-26 11:13:55', '37', '55', '2023-12-08', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(370, 32, '2023_MES_627636_1', 41, '2023-12-18 06:25:56', 'Allotted', 'GE(I)/873/EWS- /2023', 'AUGMENRATION OF 100 KVA TRANSFORMER AT DHARMUND MIL STN, PROVN OF 11KVA VCB PANEL AT CHANDERKOT AND PROVN OF 150 KVA TRANSFPRMER AND 150 KVA SERVO VLTAGE STABILIZER AT MAITRA GRN MIL STN', '659281a833c6a_DVEPL- OFFER NIT-63 of 2023-2024  6034.pdf', '6034', '54', '2024-01-01 14:41:04', 68, '2024-01-02 15:22:45', 10, '2024-07-26 11:13:55', '58', '105', '2023-12-07', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(371, 32, '2023_MES_620181_1', 41, '2023-12-18 06:26:57', 'Allotted', 'CERD/SEC/TOKEN/21 OF', 'PROVISION OF CIVIL WORKS INCLUDING SERVICES FOR PROPOSED OFFICE BLDG RCMA OF DRDL HYDERABAD', '65d33e45047f5_DVEPL- OFFER 21-CERDSEC-TOKEN- 2023-2024  6035.pdf', '6035', '71', '2024-02-19 17:10:53', 113, '2024-06-14 13:41:37', 15, '2024-07-26 11:13:55', '111', '220', '2023-12-19', NULL, NULL, NULL, '', '406000', '1', '0'),
(372, 32, '2023_MES_627932_1', 41, '2023-12-18 06:27:34', 'Allotted', 'GER-65/2023-24', 'REPAIR/MAINTENANCE OF BLDG LIGHTNING PROTECTION SYSTEM, DG SETS, SERVO VOLTAGE STABILIZER AND SECURITY LIGHT, GARDEN LIGHT, TIMER SWITCHES AND CONNECTED WORK AT NAUSHERA UNDER AGE E/M OF GE 862 EWS', '658fff9f17bbb_DVEPL- OFFER 65-GER-2023-24 6036.pdf', '6036', '54', '2023-12-30 17:01:43', 166, '2024-06-19 15:29:36', 15, '2024-07-26 11:13:55', '57', '102', '2023-12-11', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(373, 32, '2023_MES_627579_1', 41, '2023-12-18 06:28:09', 'Allotted', 'GER-64/2023-24', 'REPAIR / REPLACEMENT OF PUMP, MOTOR, STARTER, CONTROL PANELS, APFC PANELS AND CONNECTED WORK AT RAJOURI UNDER AGE E/M OF GE 862 EWS', '659e84b59e039_DVEPL- OFFER 64-GER-2023-24  6037.pdf', '6037', '51', '2024-01-10 17:21:17', 159, '2024-06-15 11:01:56', 15, '2024-07-26 11:13:55', '', '', '2023-12-09', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(374, 32, '2023_MES_628269_1', 41, '2023-12-18 06:29:22', 'Allotted', 'GE/HAL-50/2023-24', 'REPAIR MAINT OF WATER SUPPLY DISTRIBUTION PIPE LINE RISING MAIN CONTROL VALVE CLEANING OF O/H TANKS AND OTHER MISC WORKS OF WATER SUPPLY IN DOMESTIC AREA AT AF STATION HALWARA', '658ffd9fa6432_QUOTATION FOR GE-HAL-50-2023-24   6043.pdf', '6043', '55', '2023-12-30 16:53:11', 66, '2024-01-01 11:54:13', 10, '2024-07-26 11:13:55', '29', '107', '2023-12-14', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(375, 32, '2023_MES_630422_1', 41, '2023-12-18 06:30:01', 'Allotted', 'GE/BHI/CAP-03 OF 202', 'PROVISION OF AUTOMATIC POWER CONTROL UNIT PANEL AT AF STN ABOHAR UNDER GE(AF) BHISIANA', '6592809409644_DVEPL- OFFER 3-GE-BHI-CAP-2023-24   6050.pdf', '6050', '55', '2024-01-01 14:36:28', 112, '2024-06-14 12:59:46', 15, '2024-07-26 11:13:55', '30', '37', '2023-12-21', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(376, 32, '2023_MES_629302_1', 41, '2023-12-18 06:30:35', 'Allotted', 'GE(N)/M-58/2023-24', 'REPAIR/ MAINTENANCE OF ELECTRICAL E/M INSTALLATION, DG SETS, SECURITY LIGHTS AND OTHER CONNECTED WORKS AT D-IA ZONE AND D-IB ZONE UNDER AGE E/M-I OF GE (N) MAMUN', '65963dd469b8f_DVEPL- OFFER 58-GE-(N)-M-2023-24  6054.pdf', '6054', '59', '2024-01-04 10:40:44', 80, '2024-01-08 13:58:02', 10, '2024-07-26 11:13:55', '47', '82', '2023-12-18', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(377, 32, '2023_MES_631805_1', 41, '2023-12-18 06:31:15', 'Allotted', 'GE GSR-67/2023-2024', 'Provn of deficient security lights from Post No 18 to Post No 28 and shifting of transformer at Tibri Mil Stn under GE Gurdaspur.', '65840c3ed37ba_DVEPL- OFFER 67-GE GSR-2023-2024    6056.pdf', '6056', '58', '2023-12-21 15:28:22', 158, '2024-06-15 10:58:49', 15, '2024-07-26 11:13:55', '43', '70', '2023-12-28', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(378, 32, '2023_MES_631468_1', 41, '2023-12-18 06:32:22', 'Allotted', 'GE GSR-66/2023-2024', 'Spl repair to certain B/R and E/M works bldg No T-05 of OTM accn and external services of Maj Md Accn to Sub Stn 10 at Tibri Mil Stn.', '65840b4d4b042_DVEPL- OFFER 66 GE GSR-2023-2024  6057.pdf', '6057', '58', '2023-12-21 15:24:21', 154, '2024-06-15 10:08:41', 15, '2024-07-26 11:13:55', '43', '70', '2023-12-27', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(380, 32, '2023_MES_629421_1', 41, '2023-12-28 05:32:54', 'Allotted', 'CE RND/DLI-33/DLI/20', 'CONSTRUCTION OF TECHNICAL BUILDING AT CFEES UNDER GE (I) RnD DELHI', '65d341ecc3610_DVEPL- OFFER 33-CE RND-DLI-DLI2023-24  6071.pdf', '6071', '60', '2024-02-19 17:26:28', 152, '2024-06-15 10:02:48', 15, '2024-07-26 11:13:55', '52', '93', '2023-12-29', NULL, NULL, NULL, '', '2346000', '1', '0'),
(382, 32, '2023_MES_630848_1', 41, '2023-12-28 05:33:38', 'Allotted', 'CE (AF) GANDHINAGAR', 'COMPLETION OF INCOMPLETE WORK AND CERTAIN ADDITIONAL WORK FOR MODIFICATION OF FOUR DOUBLE ENTRY BLAST PENS AT AF STN JAMNAGAR (RISK AND COST)', '65927f945d428_DVEPL- OFFER 13-CE (AF) GJAM-OLD OF 2023-2024  6077.pdf', '6077', '72', '2024-01-01 14:32:12', 153, '2024-06-15 10:05:15', 15, '2024-07-26 11:13:55', '114', '224', '2023-12-29', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(384, 32, '2023_MES_631476_1', 41, '2023-12-28 05:34:21', 'Allotted', 'GE(N)/PAT- 80/2023-2', 'REPAIR MAINT AND LT CAPACITOR BANKS, SERVO STABILIZERS, LT CABLES, ROUTE INDICATOR, LT SWITCHGEARS, FEEDER PILLAR BOXES, CABLE TRAY AND AIR CIRCUIT BREAKERS AT OLD CANTT UNDER GE(NORTH) PATIALA', '658ffd235a8d7_DVEPL- OFFER 80-GE(N)-TOKEN-2023-2024  6079.pdf', '6079', '56', '2023-12-30 16:51:07', 111, '2024-06-14 12:55:14', 15, '2024-07-26 11:13:55', '37', '53', '2023-12-28', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(385, 32, '2023_MES_624820_1', 41, '2023-12-28 05:34:44', 'Allotted', 'CE CHZ/AMB/T-10/2023', 'PROVN OF OTM ACCN FOR WORKSHOP AT AMBALA CANTT', '65d334e36639a_Offer Report- PROVN OF OTM ACCN FOR WORKSHOP AT AMBALA CANTT - 23-Jan-2024 11 57 56 AM.pdf', '6080', '56', '2024-02-19 16:30:51', 106, '2024-06-14 11:37:06', 15, '2024-07-26 11:13:55', '35', '', '2024-01-13', NULL, NULL, NULL, '', '360000', '1', '0'),
(390, 32, '2023_MES_631195_1', 41, '2024-01-02 09:37:48', 'Allotted', 'GES/PKT/T-39/2023-24', 'REPR OF UPS STABILIZER MAINT OF STPS OTHER MISC EM INSTALN PERIMETER STREET SECURITY GARDEN AND GATE LIGHTS INCL CONTROL PANELS AT DHANGU MILITARY COMPLX UNDER GE S PATHANKOT', '65d3409721c11_DVEPL- OFFER 39-GES-PKT-T-2023-24  6082.pdf', '6082', '59', '2024-02-19 17:20:47', 171, '2024-06-21 09:46:18', 15, '2024-07-26 11:13:55', '48', '86', '2023-12-27', NULL, NULL, NULL, '', '78000', '1', '0'),
(392, 32, '2023_MES_632759_1', 41, '2024-01-02 09:38:43', 'Allotted', 'GE/ASR - 74 OF 2023-', 'SPECIAL REPAIR/REPLACEMENT OF OH LINE TO UG CABLE WITH LT PANEL AT VIKRAM BATRA JCO/OR MD ACCN UNDER GARRISON ENGINEER AMRITSAR CANTT', '6593fb20c0abd_DVEPL- OFFER 74-GE-ASR-2023-24  6084.pdf', '6084', '58', '2024-01-02 17:31:36', 110, '2024-06-14 12:45:24', 15, '2024-07-26 11:13:55', '43', '69', '2024-01-04', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(393, 32, '2023_MES_632356_1', 41, '2024-01-02 09:39:14', 'Allotted', 'GE/BAS-66/2023-24', 'Repair and maintenance of street light timer LT panel bus bar and high mast light and its allied services at Basoli and Janglote under GE Basoli', '65d320e9cd9d9_DVEPL- OFFER 66-GE-BAS-2023-24  6085.pdf', '6085', '59', '2024-02-19 15:05:37', 157, '2024-06-15 10:56:41', 15, '2024-07-26 11:13:55', '48', '84', '2024-01-02', NULL, NULL, NULL, '', '93000', '1', '0'),
(394, 32, '2023_MES_632174_1', 41, '2024-01-02 09:39:39', 'Allotted', 'GE/PAF-52/2023-24', 'Repair to Voltage Stabilizer UPS APFC LT Panel BUS Bar and other connected works at GE (AF) Pathankot', '65d32070ac35d_DVEPL- OFFER 52-GE-PAF-2023-24  6087.pdf', '6087', '52', '2024-02-19 15:03:36', 109, '2024-06-14 12:40:24', 15, '2024-07-26 11:13:55', '17', '9', '2024-01-02', NULL, NULL, NULL, '', '2554000', '1', '0'),
(395, 32, '2023_MES_632511_1', 41, '2024-01-02 09:40:05', 'Allotted', 'CE (CG) /VIZ/CHN/TOK', 'CONSTRUCTION OF 220 TYPE II CG MARRIED ACCOMMODATION AT 19 ACRES A1 DEFENCE LAND AT THALAKANCHERY CHENNAI STILT PLUS 5 CONFIGURATION', '6596507d450dd_DVEPL- OFFER 16-CE (CG) VIZ(CHN)TOKEN2023  6089.pdf', '6089', '70', '2024-01-04 12:00:21', 160, '2024-06-21 09:15:42', 15, '2024-07-26 11:13:55', '106', '215', '2024-01-19', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(396, 32, '2023_MES_634537_1', 41, '2024-01-02 09:40:36', 'Allotted', 'sdd', 'ddd', '65ad1700af6fd_651fa72959474_168968479937.pdf', 'dsd', '61', '2024-01-21 18:37:12', 185, '2024-06-24 16:42:29', 15, '2024-07-26 11:13:55', '', '', '2024-01-25', NULL, NULL, NULL, '', '89000', '1', '0'),
(397, 32, '2023_MES_606542_2', 41, '2024-01-02 09:42:04', 'Sent', 'CWE/JP-14/2023-24', 'PROVN OF BLACK TOP ROAD FROM RP GATE NO 2 TO KSP AT DAMANA MIL STN UNDER GE JAMMU', '65ec51611ec02_DVEPL- OFFER 14-CWE-JP-2023-24  6092.pdf', '6092', '59', '2024-03-09 17:39:05', NULL, NULL, NULL, '2024-07-25 18:48:07', '46', '79', '2023-11-25', NULL, NULL, NULL, '', '129000', '1', '0'),
(398, 32, '2023_MES_632746_1', 41, '2024-01-02 09:42:38', 'Allotted', 'GE/ASR - 73 OF 2023-', 'SPECIAL REPAIR / REPLACEMENT AND UNDERGROUND EXISTING OVERHEAD ELECTRIC TRANSMISSION LINE AT UNIT AREA OF 15 IDSR UNDER GARRISON ENGINEER AMRITSAR CANTT', '6593f82a2f4e0_DVEPL- OFFER 73-GE-ASR-2023-24  6094.pdf', '6094', '58', '2024-01-02 17:18:58', 186, '2024-06-24 16:45:52', 15, '2024-07-26 11:13:55', '43', '69', '2024-01-04', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(399, 32, '2023_MES_632755_1', 41, '2024-01-02 09:43:21', 'Allotted', 'GE(N)/AKH-48/2023-24', 'COMPREHENSIVE REPAIR/MAINT OF WATER SUPPLY PIPE LINES, VALVES, PUMPING SETS, STARTER PANELS, DOZING PLANTS, EARTHINGS, PAINTING WORKS, INSTALLATION DISTRIBUTION PANELS AND CONNECTED ITEMS AT SUNDERBANI UNDER GE (NORTH) AKHNOOR', '65b74f0e7daca_DVEPL- OFFER 48-GE(N)-AKH-2023-2024   6095.pdf', '6095', '54', '2024-01-29 12:39:02', 156, '2024-06-15 10:52:08', 15, '2024-07-26 11:13:55', '23', '', '2024-01-04', NULL, NULL, NULL, '', '451000', '1', '0'),
(400, 32, '2023_MES_633542_1', 41, '2024-01-02 09:44:00', 'Allotted', 'CWE/AMB/UTILITY-42 O', 'PROVISION OF 630 KVA TRANSFORMER ALONGWITH CONNECTED ITEMS AT OLD SYL UNDER GE (U) AMBALA CANTT', '659647aca3811_DVEPL- OFFER 42-CWE-AMB-UTILITY-2023-24  6096.pdf', '6096', '56', '2024-01-04 11:22:44', 150, '2024-06-15 09:15:43', 15, '2024-07-26 11:13:55', '35', '49', '2024-01-08', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(401, 32, '2023_MES_633180_1', 41, '2024-01-02 09:44:18', 'Allotted', 'GE(N)/M-65/2023-24', 'SPL REPAIR FOR ELECTRIC SUPPLY SYSTEM AT MES INSTALLATION UNDER AGE E/M-I OF GE (N) MAMUN', '65d3413a741be_R2-DVEPL- OFFER 65-GE(N)-M-2023-24  6097.pdf', '6097', '59', '2024-02-19 17:23:30', 108, '2024-06-14 12:06:32', 15, '2024-07-26 11:13:55', '47', '82', '2024-01-04', NULL, NULL, NULL, '', '1836000', '1', '0'),
(402, 32, '2023_MES_633180_1', 41, '2024-01-02 11:00:27', 'Allotted', 'GE(N)/M-65/2023-24', 'SPL REPAIR FOR ELECTRIC SUPPLY SYSTEM AT MES INSTALLATION UNDER AGE E/M-I OF GE (N) MAMUN', '6593fb979e0be_DVEPL- OFFER 65-GE(N)-M-2023-24  6097.pdf', '6097', '59', '2024-01-02 17:33:35', 151, '2024-06-15 09:58:48', 15, '2024-07-26 11:13:55', '47', '82', '2024-01-04', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(403, 32, '2022_MES_519315_2', 41, '2024-01-04 10:42:57', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2023-02-14', NULL, NULL, NULL, NULL, NULL, '0', '0'),
(404, 32, '2023_MES_572634_1', 41, '2024-01-04 10:44:34', 'Allotted', '18/CE RND/DLI-/CHAND', 'REPLACEMENT OF CENTRAL AC PLANTS AGAINST BER AT MPO AND TTD BLDG AND REPLACEMENT OF SUMMER APPLIANCES AGAINST BER ITEMS FOR THE YEAR 2014-15 AND 2015-16 AT ITR CHANDIPUR', '655afccf62450_DVEPL- OFFER 18-CE RNDDLI-CHANDI-2022-2...5353.pdf', '5353', '60', '2024-01-04 16:14:34', 72, '2024-01-05 15:15:17', 10, '2024-07-26 11:13:55', '54', '95', '2023-02-07', NULL, NULL, NULL, NULL, NULL, '1', '0');
INSERT INTO `user_tender_requests` (`id`, `member_id`, `tenderID`, `department_id`, `created_at`, `status`, `tender_no`, `name_of_work`, `file_name`, `reference_code`, `section_id`, `sent_at`, `selected_user_id`, `allotted_at`, `reminder_days`, `updated_at`, `division_id`, `sub_division_id`, `due_date`, `remark`, `remarked_at`, `project_status`, `file_name2`, `tentative_cost`, `auto_quotation`, `delete_tender`) VALUES
(409, 32, '2023_MES_592402_3', 41, '2024-01-04 11:39:00', 'Allotted', '18/GE(N)/M/2022-23', 'REPAIR/ MAINTENANCE/ REPLACEMENT OF LT PANELS, EARTHINGS, MCCBS, SECURITY LIGHTS AND CONNECTED ITEMS AT D-IB ZONE AND CENTRAL AMENTIES UNDER AGE E/M -I OF GE (N) MAMUN', '655b132253d17_R1-DVEPL- OFFER 18-GE-(N)M2022-23  5538.pdf', '5538', '59', '2024-01-04 17:09:00', 75, '2024-01-06 11:18:20', 10, '2024-07-26 11:13:55', '48', '85', '2023-06-14', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(412, 32, '2023_MES_595243_1', 41, '2024-01-04 11:41:47', 'Allotted', 'CWE/NGT-10/2023-24', 'SPECIAL REPAIR/REPLACEMENT OF E/M ITEMS UNDER GE NAGROTA', '655d915137d33_Offer Report- 2023_MES_595243_1  Spl repair_replacement of E_M items under GE Nagrota - 12-Jun-2023 04 15 19 PM.pdf', '5564', '54', '2024-01-04 17:11:47', 76, '2024-01-06 11:22:18', 10, '2024-07-26 11:13:55', '56', '100', '2023-06-19', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(414, 32, '2023_MES_622841_2', 41, '2024-01-05 07:19:56', 'Allotted', 'GE (I) R AND D/CHD?7', 'REPAIR AND MAINT OF LT/APFC PANELS, LT OVER HEAD LINE, SERVO VOLTAGE STABILISER, LT UG CABLES, MCCBS UPS AND OTHER ALLIED WORKS IN TBRL RANGE RAMGARH', '659e5e995a214_DVEPL- OFFER 77-GE I R AND DCHD-2023-24    6123.pdf', '6123', '60', '2024-01-10 14:38:41', 187, '2024-06-24 16:59:40', 15, '2024-07-26 11:13:55', '53', '94', '2024-01-08', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(421, 32, '2023_MES_596200_2', 41, '2024-01-05 11:52:41', 'Allotted', ' GE/U- /2023-24', 'Special Repair/Replacement of overhead electric cables to underground electric cables at Inf Bn Loc under GE (Utilities) Udhampur.', '655c9cde66213_DVEPL- OFFER DVEPL- OFFER GEU- 2023-24...5589.pdf', '5589', '54', '2024-01-05 17:22:41', 64, '2024-01-10 12:03:19', 10, '2024-07-26 11:13:55', '27', '104', '2023-07-21', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(424, 32, '2023_MES_620741_1', 41, '2024-01-06 06:31:10', 'Allotted', 'CWE/CHM(U)-66/2023-2', 'REPAIR AND MAINT OF HT 11 KV DISTRIBUTION NETWORK AND CONNECTED ITEMS OF WORKS AT MD ACCN AREAS AT CHANDIMANDIR', '667fd31e393b0_DVEPL- OFFER 66-CWE-CHM-(U)2023-24  5946.pdf', '5946', '56', '2024-06-29 14:55:50', 197, '2024-07-01 17:55:53', 15, '2024-07-26 11:13:55', '36', '52', '2023-11-09', NULL, NULL, NULL, '', '175000', '1', '0'),
(425, 32, '2023_MES_620741_1', 41, '2024-01-06 08:35:12', 'Allotted', 'CWE/CHM(U)-66/2023-2', 'REPAIR AND MAINT OF HT 11 KV DISTRIBUTION NETWORK AND CONNECTED ITEMS OF WORKS AT MD ACCN AREAS AT CHANDIMANDIR', '667d5201652af_DVEPL- OFFER 66-CWE-CHM-(U)2023-24  5946.pdf', '5946', '56', '2024-06-27 17:20:25', 192, '2024-06-28 10:28:27', 15, '2024-07-26 11:13:55', '36', '52', '2023-11-09', NULL, NULL, NULL, '', '175000', '1', '0'),
(426, 32, '2023_MES_620741_1', 41, '2024-01-06 08:36:35', 'Allotted', 'CWE/CHM(U)-66/2023-2', 'REPAIR AND MAINT OF HT 11 KV DISTRIBUTION NETWORK AND CONNECTED ITEMS OF WORKS AT MD ACCN AREA AT CHANDIMANDIR', '667fd5d77e10c_DVEPL- OFFER 66-CWE-CHM-(U)2023-24  5946.pdf', '5946', '56', '2024-06-29 15:07:27', 219, '2024-07-02 17:09:24', 15, '2024-07-26 11:13:55', '36', '52', '2023-11-09', NULL, NULL, NULL, '', '175000', '1', '0'),
(439, 32, '2023_MES_632700_1', 41, '2024-01-09 06:57:37', 'Allotted', 'GE/KC/ OF 2023-2024', 'SPL REPAIR TO LT PANEL, LT CABLE AND CONNECTED ITEMS IN SATA BTY, PROVN OF 24 EXHAUST FAN, ADDN/ALT TO WATER POINT AND PROVN OF CULVERT IN FRONT OF PT GROUND IN KALUCHAK MIL STN UNDER GE KALUCHAK', '6599182d7c340_DVEPL- OFFER 55-GE-KC-NIT-2023-24   6111.pdf', '6111', '59', '2024-01-09 12:27:37', 167, '2024-06-19 15:35:14', 15, '2024-07-26 11:13:55', '46', '80', '2024-01-06', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(442, 32, '2023_MES_634120_1', 41, '2024-01-09 09:30:59', 'Allotted', 'CWE/RAJ?46/2023?24', 'PROVN OF 100 KVA TRANSFORMER AND LT DISTRIBUTION PANEL AT 14 PUNJAB UNDER GE 862 EWS', '659d15cb684e4_DVEPL- OFFER 46-CWE-RAJ-2023-24  6116.pdf', '6116', '54', '2024-01-09 15:15:47', 60, '2024-06-19 15:36:11', 15, '2024-07-26 11:13:55', '57', '102', '2024-01-13', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(444, 73, 'CWE/ASR-46/2023-24 ,', 41, '2024-01-10 13:51:14', 'Allotted', 'ty', 'ty', '65ad17756185f_651fa72959474_168968479937.pdf', 'ty', '62', '2024-01-21 18:39:09', 98, '2024-06-13 17:10:04', 15, '2024-07-26 11:13:55', '', '', '2024-01-27', NULL, NULL, NULL, '', '5678', '1', '0'),
(451, 82, 's', 37, '2024-01-13 16:03:28', 'Allotted', 'sdsd', 'd', '65a2b4c2721b5_651fa72959474_168968479937.pdf', 'sdsddd', '65', '2024-01-13 21:35:22', 82, '2024-01-13 21:36:06', 16, '2024-07-26 11:13:55', '', '', '2024-01-20', NULL, NULL, NULL, '65a2b4c2710ee_651fa72959474_168968479937.pdf', NULL, '1', '0'),
(457, 32, '2023_MES_632408_1', 41, '2024-01-22 08:46:36', 'Allotted', 'CEPZ-26 / 2023-24', 'UPGRADATION OF OLD VINTAGE DG SET, TRANSFORMER AND CONNECTED ACCESSORIES AT VARIOUS LOCATIONS UNDER GE (SOUTH) PATHANKOT.', '667fac4b5dfb5_OFFER.pdf', '6120', '59', '2024-06-29 12:10:11', 193, '2024-06-29 12:46:00', 15, '2024-07-26 11:13:55', '48', '86', '2024-01-17', NULL, NULL, NULL, '', '321115', '1', '0'),
(458, 32, '2023_MES_634712_1', 41, '2024-01-22 08:48:25', 'Allotted', 'GE ABH-59/23-24', 'SPL REPAIRS FOR CONVERSION OF LT OVER HEAD LINE INTO LT UG CABLE AND CONNECTED MISC WORK AT INF BN-III AT MILITARY STATION ABOHAR.', '65d331a88cce4_DVEPL- OFFER 59-GE ABH-23-24    6127.pdf', '6127', '61', '2024-02-19 16:17:04', 102, '2024-06-13 18:12:42', 15, '2024-07-26 11:13:55', '61', '117', '2024-01-18', NULL, NULL, NULL, '', '350000', '1', '0'),
(459, 32, '2023_MES_634728_1', 41, '2024-01-22 08:49:45', 'Allotted', 'DELHI', 'LT PANELS', '65d33896a0b75_Offer Report- QOUTATION FOR LT PANEL - 06-Feb-2024 04 51 39 PM.pdf', '6155', '57', '2024-02-19 16:46:38', 103, '2024-06-13 18:27:32', 15, '2024-07-26 11:13:55', '', '', '2024-01-18', NULL, NULL, NULL, '', '3422800', '1', '0'),
(460, 32, '2023_MES_633055_1', 41, '2024-01-22 08:50:37', 'Allotted', 'GE U AMB T 66 OF 202', 'SPL REPAIR REPLACEMENT LT PANEL AND LT UG CABLE NETWORK WITH ALLIED WKS AT KEPTA BABYAL PUMPING STN HARDING LINES BOOSTER PUMP TW NO 52AND12 KHARGA HOUSE AND GUN HOUSE AREA AND CERTAIN MINOR WK PERTAINING TO SUMMER APPLIANCES AT MH UNDER GE U AMB', '65d339fd7da46_DVEPL- OFFER 66-GE U AMB T 2023-24    6130.pdf', '6130', '56', '2024-02-19 16:52:37', 104, '2024-06-14 09:16:59', 15, '2024-07-26 11:13:55', '35', '49', '2024-01-15', NULL, NULL, NULL, '', '1468000', '1', '0'),
(462, 32, '2023_MES_633083_1', 41, '2024-01-22 09:06:39', 'Allotted', 'GE KPT- /2023-24', 'Repair/Maintenance of External Water supply line all types of Pump sets and other allied works at Mil Stn Beas and Jandiala Guru under GE Kapurthala', '65d33b3c76bb3_DVEPL- OFFER GE KPT- 2023-24   6134.pdf', '6134', '58', '2024-02-19 16:57:56', 105, '2024-06-14 09:26:51', 15, '2024-07-26 11:13:55', '45', '78', '2024-01-15', NULL, NULL, NULL, '', '684000', '1', '0'),
(463, 32, '2024_MES_635518_1', 41, '2024-01-22 09:07:37', 'Allotted', 'GE/PKT-61/2023-24', 'SPL REPAIR TO CONVERT OVERHEAD LT LINE INTO UG LT CABLE IN TRIVENI VIHAR UNDER GE(WEST) PATHANKOT', '65ae61ee0ea23_DVEPL- OFFER 61-GE-PKT-2023-24   6138.pdf', '6138', '59', '2024-01-22 18:09:10', 101, '2024-06-13 17:49:26', 15, '2024-07-26 11:13:55', '48', '', '2024-01-25', NULL, NULL, NULL, '', '85000', '1', '0'),
(464, 32, '2024_MES_636548_1', 41, '2024-01-22 09:08:46', 'Allotted', 'CWE/ASR-46/2023-24', 'PROVN OF APFC PANEL AT ELECTRICAL SUB STATION AT NAMS', '65af3abeeeca8_DVEPL- OFFER 46-CWE-ASR-2023-24    6139.pdf', '6139', '58', '2024-01-23 09:34:14', 73, '2024-06-24 17:16:06', 15, '2024-07-26 11:13:55', '43', '71', '2024-01-29', NULL, NULL, NULL, '', '1757000', '1', '0'),
(465, 32, '2024_MES_636541_1', 41, '2024-01-22 09:13:11', 'Allotted', ' CWE/ASR-47/2023-24', 'SPL REPAIR 07 NOS VCB AGAINST BER AND ALLIED WORKS AT NAMS', '65d2fe24cd6c8_VCB OFFER 1(1250)+6 (800 )11KV - 6142.pdf', '6142', '58', '2024-02-19 12:37:16', 188, '2024-06-24 17:23:32', 15, '2024-07-26 11:13:55', '43', '71', '2024-01-29', NULL, NULL, NULL, '', '2815417', '1', '0'),
(471, 83, ' 2024_MES_637386_3', 41, '2024-01-29 05:01:46', 'Allotted', 'GE 874/EWS-71/2023-2', 'PROVN OF DG SET, INCINERATOR, HARD STANDING AND SPL REPAIR TO LT TRANSMISSION LINE UNDER AOR OF GE 874 EWS.', '65b74d4d97193_DVEPL- OFFER 71-GE 874EWS-2023-24   6158.pdf', '6158', '51', '2024-01-29 12:31:33', 95, '2024-06-13 17:00:45', 15, '2024-07-26 11:13:55', '15', '5', '2024-02-05', NULL, NULL, NULL, '', '122000', '1', '0'),
(473, 32, 'r23423353454', 38, '2024-01-30 13:59:20', 'Allotted', '45345', '3535', '65b90112ef133_KRUNAL_JOGI (1).pdf', '353535', '66', '2024-01-30 19:30:50', 32, '2024-01-30 19:31:49', 17, '2024-07-26 11:13:55', '84', '168', '2024-01-31', NULL, NULL, NULL, '65b90112eee54_Shubham Kumar.pdf', '43535435', '1', '0'),
(474, 73, '2024_MES_636590_1', 41, '2024-01-31 03:50:48', 'Sent', 'CWE/ASR-53/2023-24', ' IMPROVEMENT AND MAINT OF WATER SUPPLY DISTRIBUTION SYSTEM IN OVER HEAD TANKS RESIDENTIAL BLDGS AND OTHER MISC WORKS AT NAMS UNDER GE NAMS', '65ec50a155e96_R1-DVEPL- OFFER 53-CWE-ASR-2023-24  6167.pdf', '6167', '58', '2024-03-09 17:35:53', NULL, NULL, NULL, '2024-07-25 18:48:07', '43', '71', '2024-01-04', NULL, NULL, NULL, '', '1057000', '1', '0'),
(477, 73, '2024_MES_636671_1', 41, '2024-01-31 09:21:58', 'Allotted', 'GE/KC/NIT-68/2023-24', 'REPAIR AND MAINT OF LT OH UG CONDUCTOR CABLE, PANELS AND ASSOCIATED WKS SERVICES AT MIL STN KALUCHAK UNDER GE KALUCHAK', '65c0842777d71_DVEPL- OFFER 68-GEKCNIT-2023-24   6178.pdf', '6178', '59', '2024-02-05 12:15:59', 179, '2024-06-22 16:59:03', 15, '2024-07-26 11:13:55', '46', '80', '2024-01-04', NULL, NULL, NULL, '', '730000', '1', '0'),
(478, 83, ' 2024_MES_637828_1', 41, '2024-01-31 10:31:34', 'Allotted', 'CWE/JAL/S?71/2023?24', 'Provn of conversion of security light and head electric line into LT cable underground at Mil Stn Suranussi under AGE (I) Suranussi', '65ba0e232bfc0_DVEPL- OFFER 71-CWE-JALS-2023-24   6168.pdf', '6168', '58', '2024-01-31 16:01:34', 69, '2024-06-25 09:33:56', 15, '2024-07-26 11:13:55', '45', '74', '2024-02-12', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(479, 83, '2024_MES_635337_1', 41, '2024-01-31 10:32:04', 'Allotted', 'CE RnD]/DLI?40 /DLI/', 'PROVISION OF INTERLOCKING PAVED PATROLLING FOOTPATH AT CFEES AND PROVISION OF PAVED BLOCKS BETWEEN BUILDING AND OLD BUILDING P 8 AT CFEES UNDER GE (I) RnD DELHI', '65ba2237d3e53_DVEPL- OFFER 40 CE R and DDLI-DLI2023-2024    6110.pdf', '6110', '60', '2024-01-31 16:04:31', 190, '2024-06-25 09:38:31', 15, '2024-07-26 11:13:55', '52', '93', '2024-02-05', NULL, NULL, NULL, '', '144000', '1', '0'),
(482, 32, '2111', 39, '2024-02-01 05:36:55', 'Allotted', '423432', '424', '65c2356d19e32_In-House Training Proposal.pdf', '42424', '58', '2024-02-06 19:04:37', 90, '2024-02-06 19:05:17', 0, '2024-07-26 11:13:55', '43', '69', '2024-02-08', NULL, NULL, NULL, '', '12345', '1', '0'),
(484, 84, '2024_MES_638572_1', 41, '2024-02-08 03:35:41', 'Allotted', 'GE/PKT-67/2023-24', 'REPAIR REPLACEMENT IMPROVEMENT OF LT FEEDER PILLAR PANELS/CAPACITOR PANELS, MCCB AND ALLIED WORKS UNDER GE(WEST) PATHANKOT', '65ec4fa299475_DVEPL- OFFER 67-GE-PKT-2023-24 6154.pdf', '6154', '59', '2024-03-09 17:31:38', 191, '2024-06-25 09:41:56', 15, '2024-07-26 11:13:55', '48', '', '2024-02-13', NULL, NULL, NULL, '', '711000', '1', '0'),
(487, 221, '2023_MES_606542_2', 41, '2024-07-02 14:02:07', 'Sent', 'CWE/JP-14/2023-24', 'PROVN OF BLACK TOP ROAD FROM RP GATE NO 2 TO KSP AT DAMANA MIL STN UNDER GE JAMMU', '65ec51611ec02_DVEPL- OFFER 14-CWE-JP-2023-24  6092.pdf', '6092', '59', '2024-07-02 19:32:07', NULL, NULL, NULL, '2024-07-25 18:48:07', '46', '79', '2024-07-01', NULL, NULL, NULL, NULL, NULL, '1', '0'),
(488, 222, '2024_mes_659373_1', 41, '2024-07-03 06:49:39', 'Sent', 'GE(S)/M-09/2024-2025', 'REPAIR/MAINT/REPLACEMENT OF OLD UNSV/BURNT ELECTRICAL WIRING IN MD ACCN BHASKAR I, II & IV AND CERTAIN MD ACCN OF D-I ZONE ALONGWITH CONNECTED E/M ITEMS UNDER AOR OF GE(SOUTH) MAMUN', '6688c9a49c8ab_DVEPL- OFFER GE(S)M-09  6554.pdf', '6554', '59', '2024-07-06 10:05:48', NULL, NULL, NULL, '2024-07-25 18:48:07', '47', '83', '2024-07-03', NULL, NULL, NULL, '', '330000', '1', '0'),
(491, 83, ' 2024_MES_652117_1', 41, '2024-07-03 12:16:52', 'Sent', 'CEJZ/JBL/ 07 (T) OF ', 'PROVN OF WIDENING OF ROADS WITHIN COLLEGE PREMISES AT CMM UNDER GE EAST JABALPUR', '668622b46dab4_DVEPL- OFFER CEJZJBL07 (T) of 2024-25     6556.pdf', '6556', '77', '2024-07-04 09:49:00', NULL, NULL, NULL, '2024-07-25 18:48:07', '', '', '2024-07-05', NULL, NULL, NULL, '', '450000', '1', '0'),
(492, 226, 'rajsales- home', 56, '2024-07-03 13:11:00', 'Sent', 'RAJ SALES', 'HOME PANEL', '6695f6f940e16_Offer Report- LT PANEL - 02-Jul-2024 06 39 29 PM.pdf', '6548', '78', '2024-07-16 09:58:41', NULL, NULL, NULL, '2024-07-25 18:48:07', '', '', '2024-07-05', NULL, NULL, NULL, '', '232500', '1', '0'),
(493, 221, '2024_MES_653877_1', 41, '2024-07-03 17:59:16', 'Allotted', 'CEDZ/TOKEN-12 OF 202', 'AUGMENTATION OF ELECTRIC POWER SUPPLY EQUIPMENT UNDER GE U ELECT SUPPLY DELHI CANTT10', '669609d12fe47_DVEPL- OFFER 12- CEDZ-TOKEN-2024   6449.pdf', '6449', '57', '2024-07-16 11:19:05', 233, '2024-07-18 09:54:19', 15, '2024-07-26 11:13:55', '', '', '2024-07-01', NULL, NULL, NULL, '', '3760000', '1', '0'),
(494, 221, 'ANAPURNA HOTEL AND R', 56, '2024-07-03 18:03:26', 'Sent', 'ANAPURNA HOTEL', 'ANAPURNA HOTEL, KANDRORI', '6695f64581035_Offer Report- LT PANEL - 13-Jul-2024 04 30 31 PM.pdf', ' 6543', '78', '2024-07-16 09:55:41', NULL, NULL, NULL, '2024-07-25 18:48:07', '', '', '2024-07-04', NULL, NULL, NULL, '', '122800', '1', '0'),
(495, 221, 'BIOVONIC  AMRITSAR', 56, '2024-07-03 18:04:52', 'Sent', 'PRIVATE', 'BIOVONIC', '668cb2d3bb2d7_DRG-6359 (MAIN PANEL).pdf', '6359', '78', '2024-07-09 09:17:31', NULL, NULL, NULL, '2024-07-25 18:48:07', '', '', '2024-07-04', NULL, NULL, NULL, '', '650000', '1', '0'),
(496, 83, '2024_MES_652848_1', 41, '2024-07-04 04:38:24', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2024-07-16', NULL, NULL, NULL, '', NULL, '0', '0'),
(498, 224, 'ECR RLY', 51, '2024-07-04 05:00:58', 'Sent', 'ECR RAILWAY', 'Enquiry for supply of LT Panel ECR RLY', '668d2892c5ece_Offer Report- ECR RAILWAY - 06-Jul-2024 06 10 19 PM.pdf', '6557', '78', '2024-07-09 17:39:54', NULL, NULL, NULL, '2024-07-25 18:48:07', '', '', '2024-07-08', NULL, NULL, NULL, '', '1857300', '1', '0'),
(500, 222, '2024_MES_652848_1', 41, '2024-07-04 07:54:25', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2024-07-09', NULL, NULL, NULL, '', NULL, '0', '0'),
(502, 221, '2024_MES_649317_2', 41, '2024-07-04 08:29:17', 'Sent', 'CEJZ/JRC- /2024-25', 'ADDN/ ALTERATION TO BLDG. NO. P-1 OF MH JRC UNDER GE (EAST) JALANDHAR CANTT', '6688dcff8e1aa_DVEPL- OFFER CEJZJRC- 2024-25  6563.pdf', '6563', '58', '2024-07-06 11:28:23', NULL, NULL, NULL, '2024-07-25 18:48:07', '', '', '2024-07-16', NULL, NULL, NULL, '', '74000', '1', '0'),
(508, 223, 'Raddison Hotel, Jamm', 56, '2024-07-04 10:22:41', 'Requested', NULL, NULL, '668677f1420bc_Hotel Raddision - Park Inn jammu Railway station REVISED.rar', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2024-07-08', NULL, NULL, NULL, '', NULL, '0', '0'),
(512, 222, 'PDD- ALLIANCE - VCB ', 57, '2024-07-05 23:48:41', 'Requested', NULL, NULL, '668886599de45_GTP25.pdf', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2024-07-06', NULL, NULL, NULL, '668886599e106_ALLIANCE.pdf', NULL, '0', '0'),
(513, 222, '2023_MES_613925_1', 41, '2024-07-06 00:24:35', 'Allotted', 'GE/LDH-41/2023-24', ' SPECIAL REPAIR / REPLACEMENT OF LT PANELS AND FEEDER PANELS AT BADDOWAL AND CERTAIN SANCTIONED WORK AT DHOLEWAL UNDER GE LUDHIANA', '668b644516306_DVEPL- OFFER 41 GE-LDH-2023-24...5812.pdf', '5812', '58', '2024-07-08 09:30:05', 228, '2024-07-08 09:40:34', 15, '2024-07-26 11:13:55', '44', '', '2024-07-06', NULL, NULL, NULL, '', '1214000', '1', '0'),
(514, 221, '2024_MES_651004_1   ', 41, '2024-07-06 08:20:24', 'Allotted', 'CWEAFJ-42/2023-24', 'SPECIAL REPAIR TO PARAMETER LIGHTS AND ELECTRICAL INFRASTRUCTURE AT AF STN UDHAMPUR', '668b6545e4767_DVEPL- OFFER 42-CWE-AFJ-2023-24  6400.pdf', '6400', '52', '2024-07-08 09:34:21', 229, '2024-07-08 09:58:15', 15, '2024-07-26 11:13:55', '17', '10', '2024-05-27', 'accepted', '2024-07-15 14:20:56', NULL, '', '652000', '1', '0'),
(516, 83, ' 2024_MES_659984_1', 41, '2024-07-06 10:24:12', 'Sent', 'GE/PKT-12/2024-25', 'REPAIR/REPLACEMENT/IMPROVEMENT OF EXTERNAL ELECTRIC LT NETWORK SYSTEM AND CONNECTED WORKS IN ZONE A AND B UNDER GE(WEST) PATHANKOT', '66891ef2c1f46_DVEPL- OFFER 12- GE-PKT-2024-25   6567.pdf', '6567', '59', '2024-07-06 16:09:46', NULL, NULL, NULL, '2024-07-25 18:48:07', '48', '', '2024-07-06', NULL, NULL, NULL, '', '92000', '1', '0'),
(519, 83, ' 2024_MES_659815_1', 41, '2024-07-06 11:26:19', 'Allotted', ' GES/AKH-02/2024-25', 'SPECIAL REPAIR TO BLDG NO T 20 JCO MESS T39 T40 AND T41 TOILET AND BATHROOM T12 PM SHED T40 RATION CLOTHING AND TECH STORE AT RAKHMUTHI UNDER GE SOUTH AKHNOOR', '66892ad1774d4_DVEPL- OFFER 2- GES-AKH-2024-25   6569.pdf', '6569', '54', '2024-07-06 17:00:25', 232, '2024-07-18 09:46:37', 15, '2024-07-26 11:13:55', '23', '22', '2024-07-06', NULL, NULL, NULL, '', '24000', '1', '0'),
(521, 222, '2024_MES_660435_1', 41, '2024-07-08 13:17:48', 'Sent', ' GE/HAL-19/2024-25', 'REPAIR / REPLACEMENT OF LT ELECTRIC SYSTEM AND EARTHING INFRASTRUCTURE IN TECHNICAL AREA AT AF STN HALWARA', '6692758841c8d_DVEPL- OFFER GE-HAL-19-2024-25 6589.pdf', '6589', '55', '2024-07-13 18:09:36', NULL, NULL, NULL, '2024-07-25 18:48:07', '29', '107', '2024-07-09', NULL, NULL, NULL, '', '99000', '1', '0'),
(522, 222, '2023_MES_635127_2', 41, '2024-07-09 03:25:14', 'Sent', 'CWE/JAL/E-69/2023-24', 'Provn of 01 x Cold Room in Medical Store at Jalandhar Cantt under GE (East) JRC', '668d27b3dc95c_Offer Report- CWE_JAL_E-69_2023-24 - 09-Jul-2024 05 30 47 PM.pdf', '6572', '58', '2024-07-09 17:36:11', NULL, NULL, NULL, '2024-07-25 18:48:07', '45', '', '2024-07-10', NULL, NULL, NULL, '', '61800', '1', '0'),
(526, 83, '2024_MES_660633_1', 41, '2024-07-09 08:57:39', 'Allotted', 'CWE/JP-16/2024-25', ' SPECIAL REPAIR/REPLACEMENT OF CERTAIN BER E/M POWER SUPPLY EQUIPMENTS OF 100 KVA TRANSFORMER SERVO VOLTAGE STABILIZER AND ALLIED ACCESSORIES AT RATNUCHAK UNDER GE KALUCHAK AND KHARIAN UNDER GE JAMMU', '668cff3e481a7_DVEPL- OFFER CWE-JP-16-2024-25  6574.pdf', '6574', '59', '2024-07-09 14:43:34', 237, '2024-07-19 14:20:05', 15, '2024-07-26 11:13:55', '46', '79', '2024-07-10', NULL, NULL, NULL, '', '85000', '1', '0'),
(527, 221, '2023_MES_585415_1', 41, '2024-07-09 09:40:55', 'Allotted', 'CEPZ- 37/2022-23', 'AUGMENTATION OF EXTERNAL ELECTRIC SUPPLY UNDER GE NORTH MAMUN', '668d2e74b23ca_FINAL DVEPL- OFFER 37-CEPZ-2022-23.pdf', '5486', '59', '2024-07-09 18:05:00', 230, '2024-07-10 09:37:29', 15, '2024-07-26 11:13:55', '47', '82', '2023-05-10', 'accepted', '2024-07-15 12:30:42', NULL, '', '1100000', '1', '0'),
(538, 222, '2024_MES_661142_1', 41, '2024-07-15 07:38:59', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2024-07-16', NULL, NULL, NULL, '', NULL, '0', '0'),
(541, 223, 'Mr. Vijay (NITIN SCH', 56, '2024-07-16 11:46:17', 'Sent', 'Private', 'Mr. Vijay (NITIN SCH', '66965e272f148_Offer Report- LT PANEL - 16-Jul-2024 04 43 38 PM.pdf', '6596', '78', '2024-07-16 17:18:55', NULL, NULL, NULL, '2024-07-25 18:48:07', '', '', '2024-07-16', NULL, NULL, NULL, '', '230800', '1', '0'),
(542, 231, '2023_MES_596487_1', 41, '2024-07-16 14:22:07', 'Allotted', ' CWE/JP-05/2023-24 ', 'PROVN OF VCB AND ALLIED ITEMS FOR DC LINES AT MIRAN SAHIB MIL STN UNDER GE JAMMU', '6699f61dee526_11 KV 800 A - VCB OFFER ICOG -5832.pdf', '5832', '59', '2024-07-19 10:44:05', 236, '2024-07-19 11:49:43', 15, '2024-07-26 11:13:55', '46', '79', '2023-06-13', NULL, NULL, NULL, '', '307264', '1', '0'),
(546, 221, '2024_MES_651997_1', 41, '2024-07-22 06:43:24', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2024-05-30', NULL, NULL, NULL, '', NULL, '0', '0'),
(547, 221, '2024_MES_652007_1', 41, '2024-07-22 06:45:29', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2024-05-30', NULL, NULL, NULL, '', NULL, '0', '0'),
(548, 221, '2024_MES_652017_1', 41, '2024-07-22 06:49:16', 'Requested', NULL, NULL, '', NULL, '', NULL, NULL, NULL, NULL, '2024-07-25 18:38:29', NULL, NULL, '2024-05-30', NULL, NULL, NULL, '', NULL, '0', '0');

-- --------------------------------------------------------

--
-- Table structure for table `web_content`
--

CREATE TABLE `web_content` (
  `cont_id` int(11) NOT NULL,
  `mobile_no` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `about_us` longtext NOT NULL,
  `footer` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `web_content`
--

INSERT INTO `web_content` (`cont_id`, `mobile_no`, `email`, `about_us`, `footer`, `title`) VALUES
(13, '9417601244', 'dvepl@yahoo.in', '<p>Quote Tender&nbsp;introduce ourselves as a quick-moving, customer-focused business with a solid background in offering Engineering and Manufacturing services. For example, we install, test, commission, and run all HT/LT Panels, Relays, Automation, VCB Panels, and Retrofit Panels of 11/33 KV Sub Stations.&nbsp;</p>\r\n', '  dasdsad', 'Quote Tender');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`banner_id`);

--
-- Indexes for table `brand`
--
ALTER TABLE `brand`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `ct_user`
--
ALTER TABLE `ct_user`
  ADD PRIMARY KEY (`sno`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `division`
--
ALTER TABLE `division`
  ADD PRIMARY KEY (`division_id`),
  ADD KEY `FOREIGN KEY` (`section_id`);

--
-- Indexes for table `google_captcha`
--
ALTER TABLE `google_captcha`
  ADD PRIMARY KEY (`captcha_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `navigation_menus`
--
ALTER TABLE `navigation_menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `price_list`
--
ALTER TABLE `price_list`
  ADD PRIMARY KEY (`price_id`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `smtp_email`
--
ALTER TABLE `smtp_email`
  ADD PRIMARY KEY (`smtp_id`);

--
-- Indexes for table `sub_division`
--
ALTER TABLE `sub_division`
  ADD PRIMARY KEY (`id`),
  ADD KEY `division_id` (`division_id`);

--
-- Indexes for table `tender`
--
ALTER TABLE `tender`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_tender_requests`
--
ALTER TABLE `user_tender_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_content`
--
ALTER TABLE `web_content`
  ADD PRIMARY KEY (`cont_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `banner`
--
ALTER TABLE `banner`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `brand`
--
ALTER TABLE `brand`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `ct_user`
--
ALTER TABLE `ct_user`
  MODIFY `sno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `division`
--
ALTER TABLE `division`
  MODIFY `division_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `google_captcha`
--
ALTER TABLE `google_captcha`
  MODIFY `captcha_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;

--
-- AUTO_INCREMENT for table `navigation_menus`
--
ALTER TABLE `navigation_menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `price_list`
--
ALTER TABLE `price_list`
  MODIFY `price_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `smtp_email`
--
ALTER TABLE `smtp_email`
  MODIFY `smtp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sub_division`
--
ALTER TABLE `sub_division`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=238;

--
-- AUTO_INCREMENT for table `tender`
--
ALTER TABLE `tender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=661;

--
-- AUTO_INCREMENT for table `user_tender_requests`
--
ALTER TABLE `user_tender_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=785;

--
-- AUTO_INCREMENT for table `web_content`
--
ALTER TABLE `web_content`
  MODIFY `cont_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `division`
--
ALTER TABLE `division`
  ADD CONSTRAINT `FOREIGN KEY` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sub_division`
--
ALTER TABLE `sub_division`
  ADD CONSTRAINT `sub_division_ibfk_1` FOREIGN KEY (`division_id`) REFERENCES `division` (`division_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
