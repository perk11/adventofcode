package main

import (
	"flag"
	"fmt"
	"log"
	"math"
	"os"
	"runtime/pprof"
	"slices"
	"strconv"
	"strings"
)

type Keypress string

const (
	up    Keypress = "^"
	down  Keypress = "v"
	left  Keypress = "<"
	right Keypress = ">"
	A     Keypress = "A"
)

type Neighbor struct {
	name      string
	direction Keypress
}
type Coordinates struct {
	x, y int
}

var numericNeighborMap = map[string][]Neighbor{
	"A": {{"0", left}, {"3", up}},
	"0": {{"2", up}, {"3", right}, {"A", right}},
	"2": {{"1", left}, {"3", right}, {"0", down}, {"5", up}},
	"1": {{"4", up}, {"2", right}},
	"3": {{"2", left}, {"6", up}, {"A", down}},
	"4": {{"1", down}, {"7", up}, {"5", right}},
	"5": {{"4", left}, {"2", down}, {"6", right}, {"8", up}},
	"6": {{"5", left}, {"3", down}, {"9", up}},
	"7": {{"4", down}, {"8", right}},
	"8": {{"7", left}, {"9", right}, {"5", down}},
	"9": {{"8", left}, {"6", down}},
}
var numericDial = [][]string{
	{"7", "8", "9"},
	{"4", "5", "6"},
	{"1", "2", "3"},
	{"", "0", "A"},
}
var keypadDial = [][]string{
	{"", "^", "A"},
	{"<", "v", ">"},
}
var keyPadNeighborMap = map[string][]Neighbor{
	"<": {{"v", right}},
	"v": {{"<", left}, {"^", up}, {">", right}},
	"^": {{"v", down}, {"A", right}},
	">": {{"v", left}, {"A", up}},
	"A": {{"^", left}, {">", down}},
}
var sequenceCache map[string][][]Keypress
var cpuprofile = flag.String("cpuprofile", "", "write cpu profile to `file`")
var allKeyPressesCache map[string][][]Keypress

func main() {
	// Parse command-line flags
	flag.Parse()

	// Start CPU profiling if the -cpuprofile flag is set
	if *cpuprofile != "" {
		f, err := os.Create(*cpuprofile)
		if err != nil {
			log.Fatal("could not create CPU profile: ", err)
		}
		defer f.Close()

		if err := pprof.StartCPUProfile(f); err != nil {
			log.Fatal("could not start CPU profile: ", err)
		}
		defer pprof.StopCPUProfile()
	}
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		var numericKeyPresses [][]Keypress
		lines := strings.Split(string(fileContents), "\n")
		total := 0
		sequenceCache = make(map[string][][]Keypress)
		allKeyPressesCache = make(map[string][][]Keypress)
		for _, line := range lines {
			numbersToPress := strings.Split(line, "")
			numericKeyPresses = allKeyPressesForSequence(numbersToPress, "A", numericNeighborMap, numericDial)
			println("Found numeric key presses for " + line)
			for _, keyPresSequence := range numericKeyPresses {
				for _, keyPress := range keyPresSequence {
					print(string(keyPress))
				}
				print("\n")
			}
			previousKeyPresses := numericKeyPresses
			for lvl := 25; lvl >= 1; lvl-- {
				lvlKeyPadPresses := make([][]Keypress, 0)
				for _, previousKeyPressesList := range previousKeyPresses {
					sequence := make([]string, len(previousKeyPressesList))
					for index, Keypress := range previousKeyPressesList {
						sequence[index] = string(Keypress)
					}
					lvlForCurrentSequence := allKeyPressesForSequence(sequence, "A", keyPadNeighborMap, keypadDial)
					lvlKeyPadPresses = append(lvlKeyPadPresses, lvlForCurrentSequence...)
				}
				println("Found " + strconv.Itoa(len(lvlKeyPadPresses)) + " lvl " + strconv.Itoa(lvl) + " keypad presses for " + line)
				previousKeyPresses = lvlKeyPadPresses
			}
			minLength := math.MaxInt
			var minCode []Keypress
			for _, lvl1KeyPresses := range previousKeyPresses {
				if len(lvl1KeyPresses) < minLength {
					minLength = len(lvl1KeyPresses)
					minCode = lvl1KeyPresses
				}
			}
			for _, keyPress := range minCode {
				print(string(keyPress))
			}
			print("\n")

			numericPart, err := strconv.Atoi(line[:3])
			if err != nil {
				panic(err)
			}
			total += numericPart * minLength
		}

		println(total)
	}
}

func getKeyPressesCacheKey(buttonsToPress []string, previousButton string) string {
	return strings.Join(buttonsToPress, "") + previousButton
}
func allKeyPressesForSequence(buttonsToPress []string, previousButton string, neighborMap map[string][]Neighbor, dial [][]string) [][]Keypress {
	cacheKey := getKeyPressesCacheKey(buttonsToPress, previousButton)
	var result [][]Keypress
	result, ok := allKeyPressesCache[cacheKey]
	if ok {
		return result
	}
	for _, button := range buttonsToPress {
		currentToNextKeyPressSequences := allKeypressesForTarget(previousButton, button, neighborMap, dial)
		if len(currentToNextKeyPressSequences) == 0 {
			panic("Failed to find keypress sequences to get from " + previousButton + " to " + button)
		}
		if len(result) == 0 {
			result = currentToNextKeyPressSequences
		} else {
			newButtonsToPress := make([][]Keypress, len(result)*len(currentToNextKeyPressSequences))
			for currentKeyPressIndex, currentKeyPressSequence := range currentToNextKeyPressSequences {
				for existingIndex, existingSequence := range result {
					targetIndex := existingIndex + currentKeyPressIndex*len(result)
					newButtonsToPress[targetIndex] = make([]Keypress, len(existingSequence))
					copy(newButtonsToPress[targetIndex], existingSequence)
					newButtonsToPress[targetIndex] = append(newButtonsToPress[targetIndex], currentKeyPressSequence...)
				}
			}
			result = newButtonsToPress
		}
		previousButton = button
	}

	//TODO: optimize to return only best ones? or unique?
	allKeyPressesCache[cacheKey] = result
	return result
}

/*
+---+---+---+
| 7 | 8 | 9 |
+---+---+---+
| 4 | 5 | 6 |
+---+---+---+
| 1 | 2 | 3 |
+---+---+---+
	| 0 | A |
	+---+---+
*/

func findCoordinatesOnDial(button string, dial [][]string) Coordinates {
	for y := 0; y < len(dial); y++ {
		for x := 0; x < len(dial[y]); x++ {
			if dial[y][x] == button {
				return Coordinates{x, y}
			}
		}
	}
	panic("Could not find button " + button)
}
func getDirectionBetweenNumbers(sourceNumber string, targetNumber string, dial [][]string) []Keypress {
	directions := make([]Keypress, 0)
	sourceCoordinates := findCoordinatesOnDial(sourceNumber, dial)
	targetCoordinates := findCoordinatesOnDial(targetNumber, dial)
	if sourceCoordinates.x > targetCoordinates.x {
		directions = append(directions, left)
	} else if sourceCoordinates.x < targetCoordinates.x {
		directions = append(directions, right)
	}
	if sourceCoordinates.y > targetCoordinates.y {
		directions = append(directions, up)
	} else if sourceCoordinates.y < targetCoordinates.y {
		directions = append(directions, down)
	}

	return directions
}
func getOppositeDirection(direction Keypress) Keypress {
	if direction == up {
		return down
	}
	if direction == down {
		return up
	}
	if direction == left {
		return right
	}
	if direction == right {
		return left
	}

	panic("Can not find opposite direction for " + direction)
}

func getSequenceCacheKey(currentNumber string, targetNumber string) string {
	return currentNumber + "_" + targetNumber
}
func allKeypressesForTarget(currentNumber string, targetNumber string, neighborMap map[string][]Neighbor, dial [][]string) [][]Keypress {
	if currentNumber == targetNumber {
		return [][]Keypress{{A}}
	}
	//cacheKey := getSequenceCacheKey(currentNumber, targetNumber)
	//result, ok := sequenceCache[cacheKey]
	//if ok {
	//	return result
	//}
	neighbours := neighborMap[currentNumber]
	for _, neighbour := range neighbours {
		if neighbour.name == targetNumber {
			return [][]Keypress{{neighbour.direction, A}}
		}
	}
	var result [][]Keypress
	//result = make([][]Keypress, 0)
	directionsToNumber := getDirectionBetweenNumbers(currentNumber, targetNumber, dial)
	for _, neighbour := range neighbours {
		if !slices.Contains(directionsToNumber, neighbour.direction) {
			continue
		}

		neighbourKeypresses := allKeypressesForTarget(neighbour.name, targetNumber, neighborMap, dial)
		for _, keyPresses := range neighbourKeypresses {
			newSequence := append([]Keypress{neighbour.direction}, keyPresses...)
			result = append(result, newSequence)
		}
	}
	//sequenceCache[cacheKey] = result
	return result
}
