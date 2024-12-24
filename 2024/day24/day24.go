package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

type GateType string

const (
	AND GateType = "AND"
	OR  GateType = "OR"
	XOR GateType = "XOR"
)

type Wire struct {
	name     string
	hasValue bool
	value    bool
}
type Gate struct {
	gateType     GateType
	inputWireOne Wire
	inputWireTwo Wire
	outputWire   Wire
	isProcessed  bool
}

func main() {
	filenames := []string{"testInput", "testInput2", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		lineIndex := 0
		wires := make(map[string]Wire)
		for ; ; lineIndex++ {
			line := lines[lineIndex]
			if line == "" {
				break
			}
			value, err := strconv.Atoi(line[5:6])
			if err != nil {
				panic(err)
			}
			name := line[0:3]
			wires[name] = Wire{
				name:     name,
				hasValue: true,
				value:    value == 1,
			}
		}
		lineIndex++
		gates := make([]Gate, 0)
		gatesByInput := map[string][]Gate{}
		for ; lineIndex < len(lines); lineIndex++ {
			line := lines[lineIndex]
			fields := strings.Fields(line)
			inputWireOneName := fields[0]
			inputWireOne, ok := wires[inputWireOneName]
			if !ok {
				inputWireOne = Wire{name: inputWireOneName}
				wires[inputWireOneName] = inputWireOne
			}
			inputWireTwoName := fields[2]
			inputWireTwo, ok := wires[inputWireTwoName]
			if !ok {
				inputWireTwo = Wire{name: inputWireTwoName}
				wires[inputWireTwoName] = inputWireTwo
			}
			outputWireName := fields[4]
			outputWire, ok := wires[outputWireName]
			if !ok {
				outputWire = Wire{name: outputWireName}
				wires[outputWireName] = outputWire
			}
			newGate := Gate{
				gateType:     GateType(fields[1]),
				inputWireOne: inputWireOne,
				inputWireTwo: inputWireTwo,
				outputWire:   outputWire,
			}
			gates = append(gates, newGate)
			gatesForInputOne, ok := gatesByInput[inputWireOneName]
			if ok {
				gatesForInputOne = append(gatesForInputOne, newGate)
				gatesByInput[inputWireOneName] = gatesForInputOne
			} else {
				gatesByInput[inputWireOneName] = []Gate{newGate}
			}
			gatesForInputTwo, ok := gatesByInput[inputWireTwoName]
			if ok {
				gatesForInputTwo = append(gatesForInputTwo, newGate)
				gatesByInput[inputWireTwoName] = gatesForInputTwo
			} else {
				gatesByInput[inputWireTwoName] = []Gate{newGate}
			}
		}
		maxZ := 0
		for _, wire := range wires {
			if string(wire.name[0]) == "z" {
				wireNumber, err := strconv.Atoi(wire.name[1:3])
				if err != nil {
					panic(err)
				}
				maxZ = max(maxZ, wireNumber)
			}
		}
		for {
			for _, wire := range wires {
				if !wire.hasValue {
					continue
				}
				gates = gatesByInput[wire.name]
				for index, gate := range gates {
					if gate.isProcessed {
						continue
					}
					var otherWire Wire
					if gate.inputWireOne.name == wire.name {
						otherWire = wires[gate.inputWireTwo.name]
					} else {
						otherWire = wires[gate.inputWireOne.name]
					}
					if !otherWire.hasValue {
						continue
					}

					outValue := produceGateValue(wire.value, otherWire.value, gate)
					outWire := wires[gate.outputWire.name]
					outWire.value = outValue
					outWire.hasValue = true
					wires[gate.outputWire.name] = outWire
					gate.isProcessed = true
					gates[index] = gate
				}
				gatesByInput[wire.name] = gates
			}
			allZProcessed := true
			for _, wire := range wires {
				if !wire.hasValue && string(wire.name[0]) == "z" {
					allZProcessed = false
					break
				}
			}
			if allZProcessed {
				break
			}
		}
		total := 0
		for zIndex := 0; zIndex <= maxZ; zIndex++ {
			wireName := "z" + fmt.Sprintf("%02d", zIndex)
			wire, ok := wires[wireName]
			if !ok {
				panic(fmt.Sprintf("Wire '%s' not found", wireName))
			}
			if wire.value {
				total += 1 << zIndex
			}
		}

		println(total)
	}
}
func produceGateValue(wireOneValue bool, wireTwoValue bool, gate Gate) bool {
	switch gate.gateType {
	case AND:
		return wireOneValue && wireTwoValue
	case OR:
		return wireOneValue || wireTwoValue
	case XOR:
		return wireOneValue != wireTwoValue
	default:
		panic("Unexpected gate type")
	}
}
