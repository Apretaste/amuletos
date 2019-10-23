--
-- Table structure for table `_amulets`
--

CREATE TABLE `_amulets` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  `icon` varchar(20) NOT NULL,
  `price` float NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Effect duration in days',
  `code` varchar(20) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `_amulets`
--
ALTER TABLE `_amulets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `_amulets`
--
ALTER TABLE `_amulets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

--
-- Table structure for table `_amulets_person`
--

CREATE TABLE `_amulets_person` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `amulet_id` int(11) NOT NULL,
  `expires` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `inserted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `_amulets_person`
--

ALTER TABLE `_amulets_person`
  ADD PRIMARY KEY (`id`);

--
-- Add amulets
--

INSERT into _amulets (name, description, icon, price, duration, code) VALUES
('Detective','Entérate quién mira tu perfil. No dejes que nada se te pase por alto.','pageview','5',-1,'AMULET_DETECTIVE'),
('Shadow-Mode','Con éste amuleto podrás mantenerte oculto de las miradas ajenas, nadie encontrará tu perfil.','visibility_off','10',-1,'AMULET_SHADOWMODE'),
('Vidente','Conoce siempre quién te da dislike en Pizarra','remove_red_eye','0.10',336,'AMULET_VIDENTE'),
('El florista','Obtén el doble de flores cuando canjees en la tienda de Piropazo con este amuleto activo.','local_florist','3',168,'AMULET_FLORISTA'),
('Invitador','Recibe 30% extra en crédito cuando invites a tus amigos o familiares y estos se unan a la app','add_circle','1.50',168 ,'AMULET_INVITADOR'),
('Ticket 24/7','Gana 1 ticket para la Rifa cada 24 horas que tengas éste amuleto activo.','local_activity','3',168 ,'AMULET_TICKET247'),
('Jugador Experto','Obtén el doble de lo que ganarías normalmente en cada apuesta con éste potenciador tan especial.','videogame_asset','2',12 ,'AMULET_JUGADOR'),
('Me dicen Romeo','Al activar este amulato, su perfil aparecerá más veces en las sugerencias de Piropazo.','favorite','2',96,'AMULET_ROMEO'),
('Encuestador','Con la suerte de tu lado, obtén un regalo sorpresa al completar una encuesta.','card_giftcard','0.50',12,'AMULET_ENCUESTAS'),
('Cupones x2','Recibe el doble de ganancia al canjear un cupón mientras amuleto esté activo.','filter_2','1',12,'AMULET_CUPONESX2'),
('Encuesta x2','Recibe el doble de ganancia al completar una encuesta con éste amuleto activo.','filter_2','1',12,'AMULET_ENCUESTAX2'),
('Prioridad','Haz que tus notas sean más visibles en Pizarra y date a conocer.','stars','0.50',8,'AMULET_PRIORIDAD'),
('Apuesta Patrocinada','Cuando haces una apuesta con este amuleto activo, Apretaste duplica la cantidad jugada.','casino','2',8,'AMULET_APUESTAS');

--
-- Add codes to the inventory
--

INSERT into inventory (code, name, price, seller_id, service) VALUES
('AMULET_DETECTIVE', 'Amuleto "Detective"', 5, 'TODO', 'AMULETOS'),
('AMULET_SHADOWMODE', 'Amuleto "Shadow-Mode"', 10, 'TODO', 'AMULETOS'),
('AMULET_VIDENTE', 'Amuleto "Vidente"', 0.10, 'TODO', 'AMULETOS'),
('AMULET_FLORISTA', 'Amuleto "El florista"', 3, 'TODO', 'AMULETOS'),
('AMULET_INVITADOR', 'Amuleto "Invitador"', 1.50, 'TODO', 'AMULETOS'),
('AMULET_TICKET247', 'Amuleto "Ticket 24/7"', 3, 'TODO', 'AMULETOS'),
('AMULET_JUGADOR', 'Amuleto "Jugador Experto"', 2, 'TODO', 'AMULETOS'),
('AMULET_ROMEO', 'Amuleto "Me dicen Romeo"', 2, 'TODO', 'AMULETOS'),
('AMULET_ENCUESTAS', 'Amuleto "Encuestador"', 0.50, 'TODO', 'AMULETOS'),
('AMULET_CUPONESX2 ', 'Amuleto "Cupones x2"', 1, 'TODO', 'AMULETOS'),
('AMULET_ENCUESTAX2', 'Amuleto "Encuesta x2"', 1, 'TODO', 'AMULETOS'),
('AMULET_PRIORIDAD ', 'Amuleto "Prioridad"', 0.50, 'TODO', 'AMULETOS'),
('AMULET_APUESTAS ', 'Amuleto "Apuesta Patrocinada"', 2, 'TODO', 'AMULETOS');