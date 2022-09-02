<?php

class Lotacao {

    /**
     * Classe que abriga as várias rotina de Lotação
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    public function getLotacao($idLotacao = null) {
        /**
         * Retorna o nome da lotação
         * 
         * @syntax $this->get_dados($idRpa);
         */
        if (empty($idLotacao)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega os dados
            $select = "SELECT DIR,
                              GER
                       FROM tblotacao
                      WHERE idLotacao = $idLotacao";

            $row = $pessoal->select($select, false);
            echo $row[0] . " - " . $row[1];
        }
    }

    ###########################################################

    public function getLotacaoAnterior($idHistLot = null) {
        /**
         * Retorna a lotação anterior deste servidor a partir de uma mudança de lotação no histórico 
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select1 = "SELECT idServidor
                         FROM tbhistlot
                        WHERE idHistLot = {$idHistLot}";

            $row1 = $pessoal->select($select1, false);
            $idServidor = $row1[0];

            # Agora pega a lotação anterior a esta mudança
            $select2 = "SELECT idHistLot,
                               lotacao 
                          FROM tbhistlot 
                         WHERE idServidor = {$idServidor}
                      ORDER BY data";

            $row2 = $pessoal->select($select2);

            $lotacaoAnterior = null;
            foreach ($row2 as $item) {

                if ($item[0] == $idHistLot) {
                    break;
                } else {
                    $lotacaoAnterior = $item[1];
                }
            }

            return $lotacaoAnterior;
        }
    }

    ###########################################################

    public function getNomeLotacaoAnterior($idHistLot = null) {
        /**
         * Retorna a lotação anterior deste servidor a partir de uma mudança de lotação no histórico 
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {
            $idLotacao = $this->getLotacaoAnterior($idHistLot);

            if (empty($idLotacao)) {
                return null;
            } else {
                return $this->getLotacao($idLotacao);
            }
        }
    }

    ###########################################################

    public function exibeHistoricoLotacao($idServidor = null) {
        /**
         * Retorna a lotação anterior deste servidor a partir de uma mudança de lotação no histórico 
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idServidor)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Agora pega a lotação anterior a esta mudança
            $select = "SELECT data,
                              lotacao,
                              motivo
                         FROM tbhistlot 
                        WHERE idServidor = {$idServidor}
                     ORDER BY data";

            $row = $pessoal->select($select);
            $count = $pessoal->count($select);

            foreach ($row as $item) {
                plista(date_to_php($item["data"]) . " - " . $pessoal->get_nomeLotacao($item["lotacao"]), $item["motivo"]);
                $count--;
                if ($count > 0) {
                    hr();
                }
            }
        }
    }

    ###########################################################

    public function get_nomeDiretoria($idLotacao = null) {
        /**
         * retorna o nome completo da diretoria de um idLotação
         * 
         */
        if (empty($idLotacao)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            $nome = null;

            # Cria a função caso não seja PHP 8
            if (!function_exists('str_contains')) {

                function str_contains($string = null, $search = null) {
                    /**
                     * Procura uma string dentro de outra string maior
                     */
                    if (empty($string)) {
                        return false;
                    }

                    if (empty($search)) {
                        return false;
                    }

                    if (preg_match("/{$search}/", $string)) {
                        return true;
                    } else {
                        return false;
                    }
                }

            }

            # Verifica a sigla dessa diretoria
            $select = "SELECT DIR
                       FROM tblotacao
                      WHERE idLotacao = {$idLotacao}";

            $row1 = $pessoal->select($select, false);
            $diretoria = $row1[0];

            # Pega a lotacao que pode ter o nome da diretoria
            $select = "SELECT GER, nome
                         FROM tblotacao
                        WHERE DIR = '{$diretoria}' 
                          AND (GER = 'SECR' OR GER = 'GAB')";

            $row2 = $pessoal->select($select, false);

            if (!empty($row2['nome'])) {
                # Verifica o nome
                if (str_contains($row2['GER'], 'SECR')) {
                    $nome = substr($row2['nome'], 14);
                } elseif (str_contains($row2['GER'], 'GAB')) {
                    $nome = substr($row2['nome'], 12);
                }
            }
            return $nome;
        }
    }

    ###########################################################

    public function get_nomeDiretoriaSigla($sigla = null) {
        /**
         * retorna o nome completo da diretoria de uma sigla
         * 
         */
        if (empty($sigla)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            $nome = null;
            
            # Cria a função caso não seja PHP 8
            if (!function_exists('str_contains')) {

                function str_contains($string = null, $search = null) {
                    /**
                     * Procura uma string dentro de outra string maior
                     */
                    if (empty($string)) {
                        return false;
                    }

                    if (empty($search)) {
                        return false;
                    }

                    if (preg_match("/{$search}/", $string)) {
                        return true;
                    } else {
                        return false;
                    }
                }

            }

            # Pega a lotacao que pode ter o nome da diretoria
            $select = "SELECT GER, nome
                         FROM tblotacao
                        WHERE DIR = '{$sigla}' 
                          AND (GER = 'SECR' OR GER = 'GAB')";

            $row2 = $pessoal->select($select, false);

            if (!empty($row2['nome'])) {
                # Verifica o nome
                if (str_contains($row2['GER'], 'SECR')) {
                    $nome = substr($row2['nome'], 14);
                } elseif (str_contains($row2['GER'], 'GAB')) {
                    $nome = substr($row2['nome'], 12);
                }
            }
            return $nome;
        }
    }

    ###########################################################
}
