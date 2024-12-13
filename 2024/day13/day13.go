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
	minTokens := math.MaxInt
bPresses:
	for bPresses := 100; bPresses >= 0; bPresses-- {
		for aPresses := 0; aPresses <= 100; aPresses++ {
			y := machine.bY*bPresses + machine.aY*aPresses
			x := machine.bX*bPresses + machine.aX*aPresses
			if y > machine.targetY || x > machine.targetX {
				continue bPresses
			}
			if y == machine.targetY && x == machine.targetX {
				tokens := 3*aPresses + bPresses
				minTokens = min(minTokens, tokens)
				continue bPresses
			}
		}
	}
	return minTokens
}
