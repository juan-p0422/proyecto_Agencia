
------------------------------------------------------------------------

Acutalizacion a la base de datos para colocar empresas de Transporte y para colocar el descuento de adulto mayor.

-------------------------------------------------------------------------

ALTER TABLE Transporte
ADD COLUMN NombreEmpresa VARCHAR(120) NOT NULL DEFAULT 'Sin Asignar',
ADD INDEX idx_transporte_empresa (NombreEmpresa);

------------------------------------------------------------------------

ALTER TABLE Descuento
MODIFY COLUMN TipoHuesped ENUM('Adulto','Niño','AdultoMayor') NOT NULL;

------------------------------------------------------------------------




-----------------------------------------------------------------------

Hardcodear en tabla Hoteles

------------------------------------------------------------------------

INSERT INTO Hotel (NombreHotel, Ubicacion) VALUES
  ('Hotel Catedral Guadalajara', 'Guadalajara, Jalisco'),
  ('Hotel Chapultepec Boutique', 'Guadalajara, Jalisco'),
  ('Hotel Zapopan Plaza', 'Zapopan, Jalisco'),
  ('Hotel Tlaquepaque Colonial', 'Tlaquepaque, Jalisco'),
  ('Hotel Tonalá Tradición', 'Tonalá, Jalisco'),
  ('Hotel Tequila Real', 'Tequila, Jalisco'),
  ('Hotel Amatitán Hacienda', 'Amatitán, Jalisco'),
  ('Hotel Puerto Vallarta Malecón', 'Puerto Vallarta, Jalisco'),
  ('Hotel Marina Vallarta', 'Puerto Vallarta, Jalisco'),
  ('Hotel Nuevo Vallarta Bahía', 'Nuevo Vallarta, Nayarit/Jalisco'),
  ('Hotel Ajijic Lago', 'Ajijic, Chapala, Jalisco'),
  ('Hotel Chapala Ribera', 'Chapala, Jalisco'),
  ('Hotel Mazamitla Bosque', 'Mazamitla, Jalisco'),
  ('Hotel Tapalpa Cabañas', 'Tapalpa, Jalisco'),
  ('Hotel Sayulita Surf', 'Sayulita, Nayarit/Jalisco'),
  ('Hotel San Juan de los Lagos Basílica', 'San Juan de los Lagos, Jalisco'),
  ('Hotel Lagos de Moreno Centro', 'Lagos de Moreno, Jalisco'),
  ('Hotel Arandas Azul', 'Arandas, Jalisco'),
  ('Hotel La Barca Rivera', 'La Barca, Jalisco'),
  ('Hotel Ocotlán Industrial', 'Ocotlán, Jalisco'),
  ('Hotel Autlán Sierra', 'Autlán de Navarro, Jalisco'),
  ('Hotel Ciudad Guzmán Nevado', 'Ciudad Guzmán (Zapotlán el Grande), Jalisco'),
  ('Hotel Tepatitlán Altos', 'Tepatitlán, Jalisco'),
  ('Hotel Ameca Valle', 'Ameca, Jalisco'),
  ('Hotel Talpa del Allende Santuario', 'Talpa de Allende, Jalisco'),
  ('Hotel Mascota Tradicional', 'Mascota, Jalisco'),
  ('Hotel Etzatlán Histórico', 'Etzatlán, Jalisco'),
  ('Hotel Yahualica Tradición', 'Yahualica, Jalisco'),
  ('Hotel Teuchitlán Guachimontones', 'Teuchitlán, Jalisco'),
  ('Hotel Barra de Navidad Costa', 'Barra de Navidad, Jalisco');

  ------------------------------------------------------------------------------


------------------------------------------------------------------------------------

Hardcodear habitaciones:

------------------------------------------------------------------------------------

INSERT INTO Habitacion (IdHotel, TipoHabitacion, Precio, MaximoHuespedes) VALUES
(1, 'Sencilla', 1350.00, 2), (1, 'Doble', 1950.00, 4), (1, 'Suite', 3450.00, 8),
(2, 'Sencilla', 1450.00, 2), (2, 'Doble', 2100.00, 4), (2, 'Suite', 3650.00, 8),
(3, 'Sencilla', 1300.00, 2), (3, 'Doble', 1850.00, 4), (3, 'Suite', 3300.00, 8),
(4, 'Sencilla', 1200.00, 2), (4, 'Doble', 1750.00, 4), (4, 'Suite', 3200.00, 8),
(5, 'Sencilla', 1100.00, 2), (5, 'Doble', 1650.00, 4), (5, 'Suite', 3000.00, 8),
(6, 'Sencilla', 1600.00, 2), (6, 'Doble', 2300.00, 4), (6, 'Suite', 4000.00, 8),
(7, 'Sencilla', 1500.00, 2), (7, 'Doble', 2200.00, 4), (7, 'Suite', 3850.00, 8),
(8, 'Sencilla', 2200.00, 2), (8, 'Doble', 3200.00, 4), (8, 'Suite', 5200.00, 10),
(9, 'Sencilla', 2400.00, 2), (9, 'Doble', 3400.00, 4), (9, 'Suite', 5600.00, 10),
(10, 'Sencilla', 2100.00, 2), (10, 'Doble', 3100.00, 4), (10, 'Suite', 5200.00, 10),
(11, 'Sencilla', 1750.00, 2), (11, 'Doble', 2550.00, 4), (11, 'Suite', 4200.00, 8),
(12, 'Sencilla', 1650.00, 2), (12, 'Doble', 2400.00, 4), (12, 'Suite', 4000.00, 8),
(13, 'Sencilla', 1600.00, 2), (13, 'Doble', 2300.00, 4), (13, 'Suite', 3900.00, 8),
(14, 'Sencilla', 1700.00, 2), (14, 'Doble', 2400.00, 4), (14, 'Suite', 4100.00, 8),
(15, 'Sencilla', 2000.00, 2), (15, 'Doble', 3000.00, 4), (15, 'Suite', 5200.00, 10),
(16, 'Sencilla', 1200.00, 2), (16, 'Doble', 1750.00, 4), (16, 'Suite', 3000.00, 8),
(17, 'Sencilla', 1150.00, 2), (17, 'Doble', 1700.00, 4), (17, 'Suite', 2900.00, 8),
(18, 'Sencilla', 1050.00, 2), (18, 'Doble', 1650.00, 4), (18, 'Suite', 2800.00, 8),
(19, 'Sencilla', 1000.00, 2), (19, 'Doble', 1550.00, 4), (19, 'Suite', 2700.00, 8),
(20, 'Sencilla', 980.00, 2), (20, 'Doble', 1500.00, 4), (20, 'Suite', 2600.00, 8),
(21, 'Sencilla', 1150.00, 2), (21, 'Doble', 1700.00, 4), (21, 'Suite', 3000.00, 8),
(22, 'Sencilla', 1250.00, 2), (22, 'Doble', 1850.00, 4), (22, 'Suite', 3200.00, 8),
(23, 'Sencilla', 1300.00, 2), (23, 'Doble', 1900.00, 4), (23, 'Suite', 3300.00, 8),
(24, 'Sencilla', 1100.00, 2), (24, 'Doble', 1650.00, 4), (24, 'Suite', 2950.00, 8),
(25, 'Sencilla', 1500.00, 2), (25, 'Doble', 2200.00, 4), (25, 'Suite', 3800.00, 8),
(26, 'Sencilla', 1450.00, 2), (26, 'Doble', 2150.00, 4), (26, 'Suite', 3700.00, 8),
(27, 'Sencilla', 1000.00, 2), (27, 'Doble', 1550.00, 4), (27, 'Suite', 2800.00, 8),
(28, 'Sencilla', 990.00, 2), (28, 'Doble', 1500.00, 4), (28, 'Suite', 2700.00, 8),
(29, 'Sencilla', 1300.00, 2), (29, 'Doble', 1950.00, 4), (29, 'Suite', 3400.00, 8),
(30, 'Sencilla', 2100.00, 2), (30, 'Doble', 3100.00, 4), (30, 'Suite', 5200.00, 10);

-------------------------------------------------------------------------------------



--------------------------------------------------------------------------------------

Hardcodear Transporte:

-------------------------------------------------------------------------------------

INSERT INTO Transporte (TipoTransporte, PrecioPorPersona, NombreEmpresa) VALUES
-- Autobús
('Autobus', 350.00, 'Primera Plus'),
('Autobus', 450.00, 'ETN'),
('Autobus', 550.00, 'Vallarta Plus'),
('Autobus', 650.00, 'Omnibus de México'),
('Autobus', 750.00, 'TAP'),
('Autobus', 850.00, 'Primera Plus'),
('Autobus', 950.00, 'ETN'),
-- Avión
('Avion', 1200.00, 'Volaris'),
('Avion', 1600.00, 'Viva Aerobus'),
('Avion', 2000.00, 'Aeroméxico'),
('Avion', 2400.00, 'Volaris'),
('Avion', 2800.00, 'Aeroméxico'),
('Avion', 3200.00, 'Viva Aerobus');

-------------------------------------------------------------------------------------




--------------------------------------------------------------------------------------

Hardcodear Descuentos:

--------------------------------------------------------------------------------------

INSERT INTO Descuento (TipoHuesped, PorcentajeDescuento) VALUES
('Niño', 30.00),
('AdultoMayor', 50.00),
('Adulto', 0.00);

--------------------------------------------------------------------------------------

