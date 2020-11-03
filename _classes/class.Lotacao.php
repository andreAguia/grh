<?php

class Lotacao
{

    /**
     * Classe que abriga as várias rotina de Lotação
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    public function getLotacao($idLotacao = null)
    {
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

    public function getLotacaoAnterior($idHistLot = null)
    {
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

    public function getNomeLotacaoAnterior($idHistLot = null)
    {
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
}
