package main

import (
	"fmt"
	"math"
	"os"
	"strconv"
	"strings"
)

type Machine struct {
	aX      int
	aY      int
	bX      int
	bY      int
	targetX int
	targetY int
}

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		total := 0
		for machineIndex := 0; machineIndex < len(lines); machineIndex += 4 {
			currentMachine := Machine{}
			currentMachine.aX, currentMachine.aY = parseLine(lines[machineIndex], "X+")
			currentMachine.bX, currentMachine.bY = parseLine(lines[machineIndex+1], "X+")
			currentMachine.targetX, currentMachine.targetY = parseLine(lines[machineIndex+2], "X=")
			currentMachine.targetX += 10000000000000
			currentMachine.targetY += 10000000000000
			tokens := countMachineMinTokens(currentMachine)
			if tokens != math.MaxInt {
				total += tokens
			}
		}

		println(total)
	}
}
func parseLine(line string, xPrefix string) (int, int) {
	xPosition := strings.Index(line, xPrefix)
	if xPosition == -1 {
		panic("Could not find " + xPrefix + " in line " + line)
	}
	commaPosition := strings.Index(line, ",")
	x, err := strconv.Atoi(line[xPosition+2 : commaPosition])
	if err != nil {
		panic(err)
	}
	y, err := strconv.Atoi(line[commaPosition+4:])
	if err != nil {
		panic(err)
	}
	return x, y
}
func countMachineMinTokens(machine Machine) int {
	determinant := float64(machine.aX*machine.bY - machine.aY*machine.bX)
	if determinant != 0 {
		//there is a single solution
		aPresses := float64(machine.targetX*machine.bY-machine.targetY*machine.bX) / determinant
		bPresses := float64(machine.targetY*machine.aX-machine.aY*machine.targetX) / determinant
		if aPresses-math.Trunc(aPresses) > 0.0000001 || bPresses-math.Trunc(bPresses) > 0.000001 {
			return math.MaxInt
		}
		if bPresses >= 0 && bPresses > 0 {
			return int(aPresses)*3 + int(bPresses)
		} else {
			return math.MaxInt
		}
	} else {
		panic("There is more than 1 solution here, this is not implemented")
	}
}
