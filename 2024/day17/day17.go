package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

var A, B, C, pointer int
var output []int
var instructions []int

type Operand int

const (
	op_0 Operand = 0
	op_1 Operand = 1
	op_2 Operand = 2
	op_3 Operand = 3
	op_A Operand = 4
	op_B Operand = 5
	op_C Operand = 6
)

type Instruction int

const (
	inst_adv = 0
	inst_bxl = 1
	inst_bst = 2
	inst_jnz = 3
	inst_bxc = 4
	inst_out = 5
	inst_bdv = 6
	inst_cdv = 7
)

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		output = make([]int, 0)
		lines := strings.Split(string(fileContents), "\n")
		if len(lines) != 5 {
			panic("Unexpected number of lines in the input, expecting 5, got: " + strconv.Itoa(len(lines)))
		}
		A = parseRegisterValueFromLine(lines[0])
		B = parseRegisterValueFromLine(lines[1])
		C = parseRegisterValueFromLine(lines[2])
		pointer = 0
		programText := lines[4][9:]
		instructionsStr := strings.Split(programText, ",")
		instructions = make([]int, len(instructionsStr))
		for index, instructionStr := range instructionsStr {
			instructionInt, err := strconv.Atoi(instructionStr)
			if err != nil {
				panic(err)
			}
			if instructionInt < 0 || instructionInt > 7 {
				panic("Invalid instruction: " + instructionStr)
			}
			instructions[index] = instructionInt
		}
		println("Starting interpreting")
		for index := 0; cpuCycle(); index++ {
			//println(index)
		}

		outputStr := make([]string, len(output))
		for i, num := range output {
			outputStr[i] = strconv.Itoa(num)
		}
		println(strings.Join(outputStr, ","))
	}
}
func cpuCycle() bool {
	if pointer >= len(instructions) {
		return false
	}
	instruction := Instruction(instructions[pointer])
	operand := Operand(instructions[pointer+1])
	switch instruction {
	case inst_adv:
		adv(operand)
		break
	case inst_bxl:
		bxl(operand)
		break
	case inst_bst:
		bst(operand)
		break
	case inst_jnz:
		jnz(operand)
		break
	case inst_bxc:
		bxc(operand)
		break
	case inst_out:
		out(operand)
		break
	case inst_bdv:
		bdv(operand)
		break
	case inst_cdv:
		cdv(operand)
		break
	}
	pointer += 2
	return true
}
func parseRegisterValueFromLine(line string) int {
	value, err := strconv.Atoi(line[12:])
	if err != nil {
		panic(err)
	}
	return value
}
func comboOperandToValue(operand Operand) int {
	switch operand {
	case op_A:
		return A
	case op_B:
		return B
	case op_C:
		return C
	case op_0:
		return 0
	case op_1:
		return 1
	case op_2:
		return 2
	case op_3:
		return 3
	}
	panic("invalid combo operand: " + strconv.Itoa(int(operand)))
}
func modulo83bit(value int) int {
	return value & 0b111
}
func adv(operand Operand) {
	operandValue := comboOperandToValue(operand)
	A = A / (1 << operandValue)
}
func bdv(operand Operand) {
	operandValue := comboOperandToValue(operand)
	B = A / (1 << operandValue)
}
func cdv(operand Operand) {
	operandValue := comboOperandToValue(operand)
	C = A / (1 << operandValue)
}
func bxl(operand Operand) {
	B = B ^ int(operand)
}
func bst(operand Operand) {
	operandValue := comboOperandToValue(operand)
	B = modulo83bit(operandValue)
}
func jnz(operand Operand) {
	if A != 0 {
		pointer = int(operand) - 2 // -2 is to account for the pointer moving forward by 2 after instruction
	}
}
func bxc(_ Operand) {
	B = B ^ C
}
func out(operand Operand) {
	operandValue := comboOperandToValue(operand)
	output = append(output, modulo83bit(operandValue))
}
