<?php

namespace App\LimaBundle\Scaffold\Postgres;

use App\LimaBundle\Scaffold\ConnexionDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UtilitairePostgresDatabase
{
	// ------ Lister les bases de donnees -------
	public function listerDatabases()
	{
		$ConnexionDatabase = new ConnexionDatabase;
		$sql = "SELECT datname FROM pg_database WHERE datistemplate = false ORDER BY datname";
		$stmt = $ConnexionDatabase->db_connect()->prepare($sql);
		$stmt->execute();

		for ($i = 1, $stmt = $stmt->fetchAll(), $c = count($stmt); $i < $c; $i++) {
			$stmt['0'][] = current($stmt[$i]);
		}
		return $stmt['0'];
	}
	// ------ Lister les bases de donnees -------

	// ------ Lister les tables de la BD --------
	public function listerTables()
	{
		$ConnexionDatabase = new ConnexionDatabase;
		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name";
		$stmt = $ConnexionDatabase->db_connect()->prepare($sql);
		$stmt->execute();

		$nom_table['table_names'] = array();
		foreach ($stmt->fetchAll() as $row) {
			if (!isset($key)) {
				if (isset($row['table_name'])) {
					$key = 'table_name';
				} elseif (isset($row['TABLE_NAME'])) {
					$key = 'TABLE_NAME';
				} else {
					$key = array_keys($row);
					$key = array_shift($key);
				}
			}
			$nom_table['table_names'][] = $row[$key];
		}
		return $nom_table['table_names'];
	}
	// ------ Lister les tables de la BD --------

	// ----- Lister les champs de la table ------
	public function listerChamps($table)
	{
		$ConnexionDatabase = new ConnexionDatabase;
		$sql = "SELECT col.attname, t.typname FROM pg_attribute as col INNER JOIN pg_class as tbl ON col.attrelid = tbl.oid INNER JOIN pg_type as t ON t.oid = col.atttypid WHERE tbl.relname = '$table' AND col.attnum > 0";
		$stmt = $ConnexionDatabase->db_connect()->prepare($sql);
		$stmt->execute();
		$nom_champ['field_names'][$table] = array();
		foreach ($stmt->fetchAll() as $row) {
			if (!isset($key)) {
				if (isset($row['column_name'])) {
					$key = 'column_name';
				} elseif (isset($row['COLUMN_NAME'])) {
					$key = 'COLUMN_NAME';
				} else {
					$key = key($row);
				}
			}
			$nom_champ['field_names'][$table][] = $row[$key];
		}
		return $nom_champ['field_names'][$table];
	}
	// ----- Lister les champs de la table ------

	// ------- Afficher le type du champ --------
	public function afficherTypeChamp($table, $column_name)
	{
		$ConnexionDatabase = new ConnexionDatabase;
		$sql = "SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'public' AND table_name = '$table' AND column_name = '$column_name' ORDER BY ordinal_position";
		$stmt = $ConnexionDatabase->db_connect()->prepare($sql);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$data_type = $row['data_type'];
		}
		return $data_type;
	}
	// ------- Afficher le type du champ --------

	// ------ Afficher les champs et datas ------
	public function findChampDataTable($table)
	{
		$ConnexionDatabase = new ConnexionDatabase;
		$stmt = $ConnexionDatabase->db_connect()->prepare("SELECT column_name, data_type, is_nullable, character_maximum_length FROM information_schema.columns WHERE table_schema = 'public' AND table_name = '$table' ORDER BY ordinal_position");
		$stmt->execute();

		$contenu = "";
		$key = "";

		$key .= "DROP TABLE IF EXISTS " . $table . ";\n";
		$key .= "CREATE TABLE IF NOT EXISTS " . $table . " (\n";

		while ($row = $stmt->fetch()) {
			$key .= $row['column_name'] . ' ';
			$contrainte_key = $row['column_name'];

			// Contrainte PRIMARY KEY		               
			$stmt2 = $ConnexionDatabase->db_connect()->prepare("SELECT pk.constraint_type, c.column_name FROM information_schema.table_constraints pk, information_schema.key_column_usage c WHERE pk.table_name = '$table' AND c.column_name = '$contrainte_key' AND constraint_type = 'PRIMARY KEY' AND c.table_name = pk.table_name AND c.constraint_name = pk.constraint_name");
			$stmt2->execute();
			$primary_key = $stmt2->fetch();

			// Contrainte UNIQUE
			$stmt3 = $ConnexionDatabase->db_connect()->prepare("SELECT pk.constraint_type, c.column_name FROM information_schema.table_constraints pk, information_schema.key_column_usage c WHERE pk.table_name = '$table' AND c.column_name = '$contrainte_key' AND constraint_type = 'UNIQUE' AND c.table_name = pk.table_name AND c.constraint_name = pk.constraint_name");
			$stmt3->execute();
			$unique_key = $stmt3->fetch();

			// Contrainte FOREIGN KEY
			$stmt4 = $ConnexionDatabase->db_connect()->prepare("SELECT pk.constraint_type, c.column_name FROM information_schema.table_constraints pk, information_schema.key_column_usage c WHERE pk.table_name = '$table' AND c.column_name = '$contrainte_key' AND constraint_type = 'FOREIGN KEY' AND c.table_name = pk.table_name AND c.constraint_name = pk.constraint_name");
			$stmt4->execute();
			$foreign_key = $stmt4->fetch();

			if ($row['data_type'] == "integer") {

				if ($unique_key == true) {

					$key .= $row['data_type'] . " " . $unique_key['constraint_type'];

					if ($row['is_nullable'] == "NO") {
						$key .= " NOT NULL";
					} else {
						$key .= " NULL";
					}
				} elseif ($primary_key == true) {

					$key .= "SERIAL " . $primary_key['constraint_type'];

					if ($row['is_nullable'] == "NO") {
						$key .= " NOT NULL";
					} else {
						$key .= " NULL";
					}
				} elseif ($foreign_key == true) {

					if ($row['is_nullable'] == "NO") {
						$nullable = "NOT NULL,";
					} else {
						$nullable = "NULL,";
					}

					$key .= $row['data_type'] . " " . $nullable . " " . $foreign_key['constraint_type'] . " (" . $foreign_key['column_name'] . ") REFERENCES " . substr($foreign_key['column_name'], 0, -3) . "s";
				} else {

					$key .= $row['data_type'];

					if ($row['is_nullable'] == "NO") {
						$key .= " NOT NULL";
					} else {
						$key .= " NULL";
					}
				}
			} else {

				if ($unique_key == true) {

					$key .= $row['data_type'] . '(' . $row['character_maximum_length'] . ') ' . $unique_key['constraint_type'];

					if ($row['is_nullable'] == "NO") {
						$key .= " NOT NULL";
					} else {
						$key .= " NULL";
					}
				} elseif ($primary_key == true) {

					$key .= $row['data_type'] . '(' . $row['character_maximum_length'] . ') ' . $primary_key['constraint_type'];

					if ($row['is_nullable'] == "NO") {
						$key .= " NOT NULL";
					} else {
						$key .= " NULL";
					}
				} elseif ($foreign_key == true) {

					if ($row['is_nullable'] == "NO") {
						$nullable = "NOT NULL,";
					} else {
						$nullable = "NULL,";
					}

					$key .= $row['data_type'] . '(' . $row['character_maximum_length'] . ') ' . $nullable . ' ' . $foreign_key['constraint_type'] . " (" . $foreign_key['column_name'] . ") REFERENCES " . substr($foreign_key['column_name'], 0, -3) . "s";
				} else {

					if (!$row['character_maximum_length']) {

						$key .= $row['data_type'];

						if ($row['is_nullable'] == "NO") {
							$key .= " NOT NULL";
						} else {
							$key .= " NULL";
						}
					} else {

						$key .= $row['data_type'] . '(' . $row['character_maximum_length'] . ')';

						if ($row['is_nullable'] == "NO") {
							$key .= " NOT NULL";
						} else {
							$key .= " NULL";
						}
					}
				}
			}
			$key .= ",\n";
		}

		// Enlever la derniere virgule
		$key = substr($key, 0, -2);
		$key .= "\n);";
		$contenu .= $key;

		return $contenu;
	}
	// ------ Afficher les champs et datas ------

	// -------- Executer la requete SQL ---------
	public function executerSql($sql)
	{
		// Mettre les requetes dans un tableau
		$requete = explode(";", $sql);

		// Compter le nombre de requete
		$count = count(explode(";", $sql));

		// Executer les requetes dans une boucle
		for ($i = 0; $i < $count - 1; $i++) {
			$ConnexionDatabase = new ConnexionDatabase;
			$stmt = $ConnexionDatabase->db_connect()->prepare($requete[$i]);
			$query = $stmt->execute();
		}
		return $query;
	}
	// -------- Executer la requete SQL ---------

	// ------ Afficher les alter_table_db -------
	public function alterTableDb($table)
	{
		$id = "_id";
		$columnkey = "_nomColonne_key";
		$contenu = "";
		$contenu .= " --- Exemples de types de champs : --- \n";
		$contenu .= "id SERIAL PRIMARY KEY;\n";
		$contenu .= "nomColonne UNIQUE;\n";
		$contenu .= "nomColonne INTEGER;\n";
		$contenu .= "nomColonne TEXT;\n";
		$contenu .= "nomColonne VARCHAR(255);\n";
		$contenu .= "nomColonne DATE;\n";
		$contenu .= "nomColonne TIMESTAMP WITHOUT TIME ZONE;\n";
		$contenu .= "nomColonne JSON;\n";
		$contenu .= "nomColonne FLOAT;\n";
		$contenu .= "nomColonne BOOLEAN;\n";
		$contenu .= " etc... \n";
		$contenu .= " --- Les alters tables : --- \n";
		$contenu .= "ALTER TABLE $table ADD UNIQUE (nomColonne);\n";
		$contenu .= "ALTER TABLE $table ADD COLUMN nomColonne VARCHAR(2) NOT NULL;\n";
		$contenu .= "ALTER TABLE $table DROP COLUMN nomColonne;\n";
		$contenu .= "ALTER TABLE $table DROP CONSTRAINT $table$columnkey;\n";
		$contenu .= "ALTER TABLE $table RENAME COLUMN oldColonne TO newColonne;\n";
		$contenu .= "ALTER TABLE $table RENAME TO newTable;\n";
		$contenu .= "ALTER TABLE $table ALTER COLUMN nomColonne TYPE INT USING nomColonne::integer;\n";
		$contenu .= "ALTER TABLE $table ALTER COLUMN nomColonne TYPE VARCHAR(2);\n";
		$contenu .= "ALTER TABLE $table ADD CONSTRAINT nomColonne UNIQUE (nomColonne);\n";
		$contenu .= "ALTER TABLE $table ADD FOREIGN KEY ($table$id) REFERENCES $table;\n";
		$contenu .= "ALTER TABLE $table ADD FOREIGN KEY ($table$id) REFERENCES $table ON DELETE CASCADE;\n";
		$contenu .= "ALTER TABLE $table ADD FOREIGN KEY ($table$id) REFERENCES $table ON DELETE RESTRICT;\n";
		$contenu .= "ALTER TABLE $table ADD PRIMARY KEY (id);";

		return $contenu;
	}
	// ------ Afficher les alter_table_db -------

	// ------ Supprimer toutes les tables -------
	public function deleteAllTableDb()
	{
		$utilitaireDatabase = new UtilitairePostgresDatabase();
		$count = count($utilitaireDatabase->listerTables());
		$tableau = "";

		for ($i = 0; $i < $count; $i++) {
			$droptable = $utilitaireDatabase->listerTables();
			$tableau .= $droptable[$i] . ', ';
		}

		// Enlever la derniere virgule
		$tableau = substr($tableau, 0, -2);

		$contenu = "";
		$contenu .= "DROP TABLE IF EXISTS " . $tableau . ";\n";

		return $contenu;
	}
	// ------ Supprimer toutes les tables -------

	// ----- Creer les tables Role et Users -----
	public function creerTableRoleUser()
	{
		$contenu = "";
		$contenu .= "CREATE TABLE IF NOT EXISTS utilisateurs (\n";
		$contenu .= "id SERIAL PRIMARY KEY NOT NULL,\n";
		$contenu .= "username character varying(255) UNIQUE NOT NULL,\n";
		$contenu .= "password character varying(255) NOT NULL,\n";
		$contenu .= "plainpassword character varying(255) NULL,\n";
		$contenu .= "nom character varying(255) NULL,\n";
		$contenu .= "prenom character varying(255) NULL,\n";
		$contenu .= "roles json NULL,\n";
		$contenu .= "activer boolean NULL\n";
		$contenu .= ");\n";
		$contenu .= "CREATE TABLE IF NOT EXISTS roles (\n";
		$contenu .= "id SERIAL PRIMARY KEY NOT NULL,\n";
		$contenu .= "role character varying(50) UNIQUE NOT NULL,\n";
		$contenu .= "etiquette character varying(255) NOT NULL\n";
		$contenu .= ");";

		return $contenu;
	}
	// ----- Creer les tables Role et Users -----

	// ----- Creer formulaire envoi message -----
	public function creerFormEnvoiMessage()
	{
		$contenu = "";
		$contenu .= "CREATE TABLE IF NOT EXISTS messages (\n";
		$contenu .= "id SERIAL PRIMARY KEY NOT NULL,\n";
		$contenu .= "objet character varying(255) NOT NULL,\n";
		$contenu .= "expediteur character varying(255) NOT NULL,\n";
		$contenu .= "destinataire character varying(255) NOT NULL,\n";
		$contenu .= "message text NOT NULL\n";
		$contenu .= ");\n";

		return $contenu;
	}
	// ----- Creer formulaire envoi message -----

	// ------- Exporter toutes les tables -------
	public function exporterTables()
	{
		$utilitaireDatabase = new UtilitairePostgresDatabase();
		$count = count($utilitaireDatabase->listerTables());
		$export = "";

		for ($i = 0; $i < $count; $i++) {
			$table = $utilitaireDatabase->listerTables();
			$export .= $utilitaireDatabase->findChampDataTable($table[$i]);
			$export .= "\n";
		}

		return $export;
	}
	// ------- Exporter toutes les tables -------

	// ------ Renommer une base de donnees ------
	public function RenameDatabase()
	{
		$contenu = "";
		$contenu .= "ALTER DATABASE oldDatabase RENAME TO newDatabase;\n";

		return $contenu;
	}
	// ------ Renommer une base de donnees ------

	// ------ Exporter les donnees en CSV -------
	public function arrayToCsv($array, $download = "")
	{
		$response = new StreamedResponse();

		ob_start();
		$f = fopen('php://output', 'w') or exit("Impossible d'ouvrir php://output");
		$n = 0;
		foreach ($array as $line) {
			$n++;
			if (!fputcsv($f, $line, ";")) {
				exit("Impossible d'écrire la ligne $n: $line");
			}
		}
		fclose($f) or exit("Impossible de fermer php://output");
		$str = ob_get_contents();
		ob_end_clean();

		if ($download == "") {
			return False;
		} else {
			return $str;
		}
	}
	// ------ Exporter les donnees en CSV -------

	// ------ Exporter les donnees en CSV -------
	public function queryToCsv($query, $headers, $download = "") // $headers = TRUE
	{
		$utilitaireDatabase = new UtilitairePostgresDatabase;

		$colonnes = $utilitaireDatabase->listerChamps($headers);
		$array = array();
		$line = array();

		foreach ($colonnes as $name) {
			$line[] = $name;
		}

		$array[] = $line;

		foreach ($query->fetchAll() as $row) {
			$line = array();
			foreach ($row as $item) {
				$line[] = $item;
			}
			$array[] = $line;
		}

		echo $utilitaireDatabase->arrayToCsv($array, $download);
	}
	// -------- Exporter les donnees en CSV ---------
}
